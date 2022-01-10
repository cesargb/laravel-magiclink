<?php

namespace MagicLink;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use MagicLink\Actions\ActionAbstract;
use MagicLink\Events\MagicLinkWasCreated;
use MagicLink\Events\MagicLinkWasVisited;

/**
 * @property string $token
 * @property Carbon|null $available_at
 * @property int|null $max_visits
 * @property \MagicLink\Actions\ActionAbstract $action
 * @property-read string $url
 */
class MagicLink extends Model
{
    use AccessCode;

    public function getAccessCode(): ?string
    {
        return $this->access_code;
    }

    public function getMagikLinkId(): string
    {
        return $this->getKey();
    }

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    protected static function getTokenLength()
    {
        return config('magiclink.token.length', 64) <= 255
            ? config('magiclink.token.length', 64)
            : 255;
    }

    public function getActionAttribute($value)
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return unserialize(base64_decode($value));
        }

        return unserialize($value);
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = $this->getConnection()->getDriverName() === 'pgsql'
                                        ? base64_encode(serialize($value))
                                        : serialize($value);
    }

    public function getUrlAttribute()
    {
        return url(sprintf(
            '%s/%s:%s',
            config('magiclink.url.validate_path', 'magiclink'),
            $this->id,
            $this->token
        ));
    }

    /**
     * Create makiglink.
     *
     * @return self
     */
    public static function create(ActionAbstract $action, ?int $lifetime = 4320, ?int $numMaxVisits = null)
    {
        self::deleteMagicLinkExpired();

        $magiclink = new static();

        $magiclink->token = Str::random(self::getTokenLength());
        $magiclink->available_at = $lifetime
                                    ? Carbon::now()->addMinutes($lifetime)
                                    : null;
        $magiclink->max_visits = $numMaxVisits;
        $magiclink->action = $action;

        $magiclink->save();

        $magiclink->action = $action->setMagicLinkId($magiclink->id);

        $magiclink->save();

        Event::dispatch(new MagicLinkWasCreated($magiclink));

        return $magiclink;
    }

    /**
     * Protect the Action with an access code.
     */
    public function protectWithAccessCode(string $accessCode): self
    {
        $this->access_code = Hash::make($accessCode);

        $this->save();

        return $this;
    }

    /**
     * Execute Action.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function run()
    {
        return $this->action->run();
    }

    /**
     * Call when magiclink has been visited.
     *
     * @return void
     */
    public function visited()
    {
        try {
            $this->increment('num_visits');
        } catch (QueryException $e) {
            // catch exceptino if fails to increment num_visits
        }

        Event::dispatch(new MagicLinkWasVisited($this));
    }

    /**
     * Get valid MagicLink by token.
     *
     * @param  string  $token
     * @return \MagicLink\MagicLink|null
     */
    public static function getValidMagicLinkByToken($token)
    {
        [$tokenId, $tokenSecret] = explode(':', "{$token}:");

        if (empty($tokenSecret)) {
            return;
        }

        return self::where('id', $tokenId)
                    ->where('token', $tokenSecret)
                    ->where(function ($query) {
                        $query
                            ->whereNull('available_at')
                            ->orWhere('available_at', '>=', Carbon::now());
                    })
                    ->where(function ($query) {
                        $query
                            ->whereNull('max_visits')
                            ->orWhereRaw('max_visits > num_visits');
                    })
                    ->first();
    }

    /**
     * Get MagicLink by token.
     *
     * @param  string  $token
     * @return \MagicLink\MagicLink|null
     */
    public static function getMagicLinkByToken($token)
    {
        [$tokenId, $tokenSecret] = explode(':', "{$token}:");

        if (empty($tokenSecret)) {
            return;
        }

        return self::where('id', $tokenId)
                    ->where('token', $tokenSecret)
                    ->first();
    }

    /**
     * Delete magiclink was expired.
     *
     * @return void
     */
    public static function deleteMagicLinkExpired()
    {
        self::where(function ($query) {
            $query
                ->where('available_at', '<', Carbon::now())
                ->orWhere(function ($query) {
                    $query
                        ->whereNotNull('max_visits')
                        ->whereRaw('max_visits <= num_visits');
                });
        })
        ->delete();
    }

    /**
     * Delete all MagicLink.
     *
     * @return void
     */
    public static function deleteAllMagicLink()
    {
        self::truncate();
    }
}
