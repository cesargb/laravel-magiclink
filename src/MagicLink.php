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
use MagicLink\Events\MagicLinkWasDeleted;
use MagicLink\Events\MagicLinkWasVisited;
use MagicLink\Exceptions\LegacyActionFormatException;
use MagicLink\Security\Serializable\ActionSerializable;

/**
 * @property string $token
 * @property Carbon|null $available_at
 * @property int|null $max_visits
 * @property int|null $num_visits
 * @property \MagicLink\Actions\ActionAbstract $action
 * @property-read string $url
 * @property int|string $access_code
 */
class MagicLink extends Model
{
    use AccessCode;

    public function getAccessCode()
    {
        return $this->access_code ?? null;
    }

    public function getMagikLinkId()
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
        try {
            $action = ActionSerializable::unserialize($value);
        } catch (\Exception $e) {
            throw LegacyActionFormatException::detected($e);
        }

        if (! $action instanceof ActionAbstract) {
            throw new \RuntimeException('Invalid action type. Only ActionAbstract instances are allowed.');
        }

        return $action;
    }

    public function setActionAttribute($value)
    {
        if (! $value instanceof ActionAbstract) {
            throw new \InvalidArgumentException('Only ActionAbstract instances can be stored as actions.');
        }

        $this->attributes['action'] = ActionSerializable::serialize($value);
    }

    public function baseUrl(?string $baseUrl): self
    {
        $this->attributes['base_url'] = rtrim($baseUrl, '/').'/';

        return $this;
    }

    public function getUrlAttribute(): string
    {
        $baseUrl = rtrim($this->attributes['base_url'] ?? '', '/').'/'; // Use the stored base_url or an empty string

        return url(sprintf(
            '%s%s/%s%s%s',
            $baseUrl,
            config('magiclink.url.validate_path', 'magiclink'),
            $this->id,
            urlencode(':'),
            $this->token
        ));
    }

    /**
     * Create MagicLink.
     *
     * @return self
     */
    public static function create(ActionAbstract $action, ?int $lifetime = 4320, ?int $numMaxVisits = null)
    {
        static::deleteMagicLinkExpired();

        $magiclink = new static;

        $magiclink->token = Str::random(static::getTokenLength());
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
            return null;
        }

        return static::where('id', $tokenId)
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
            return null;
        }

        return static::where('id', $tokenId)
            ->where('token', $tokenSecret)
            ->first();
    }

    /**
     * Delete MagicLink was expired.
     *
     * @return void
     */
    public static function deleteMagicLinkExpired()
    {
        $query = MagicLink::where(function ($query) {
            $query
                ->where('available_at', '<', Carbon::now())
                ->orWhere(function ($query) {
                    $query
                        ->whereNotNull('max_visits')
                        ->whereRaw('max_visits <= num_visits');
                });
        });

        if (config('magiclink.delete_massive', true)) {
            $query->delete();

            return;
        }

        $query->get()->each(function (MagicLink $magiclink) {
            $magiclink->delete();

            event(new MagicLinkWasDeleted($magiclink));
        });
    }

    /**
     * Delete all MagicLink.
     *
     * @return void
     */
    public static function deleteAllMagicLink()
    {
        static::truncate();
    }
}
