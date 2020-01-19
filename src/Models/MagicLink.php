<?php

namespace Cesargb\MagicLink\Models;

use Carbon\Carbon;
use Cesargb\MagicLink\Actions\Action;
use Cesargb\MagicLink\Actions\ActionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MagicLink extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function getActionAttribute($value)
    {
        return unserialize($value);
    }

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = serialize($value);
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
     * Create a magiclink.
     *
     * @param int|null $lifetime
     * @param int|null $numMaxVisits
     * @return Cesargb\MagicLink\Models\MagicLink;
     */
    public static function create(ActionInterface $action, ?int $lifetime = 4320, ?int $numMaxVisits = null)
    {
        self::deleteMagicLinkExpired();

        $magiclink = new self();

        $magiclink->token = Str::random(config('magiclink.token.length', 64));

        if ($lifetime) {
            $magiclink->available_at = Carbon::now()->addMinute($lifetime);
        }

        if ($numMaxVisits) {
            $magiclink->max_visits = (int) $numMaxVisits;
        }

        $magiclink->action = $action;
        $magiclink->save();

        return $magiclink;
    }

    /**
     * Execute Action.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function run()
    {
        $this->increment('num_visits');

        return $this->action->run();
    }

    /**
     * Get valid MagicLink by token.
     *
     * @param string $token
     * @return null|\Cesargb\MagicLink\Models\MagicLink
     */
    public static function getValidMagicLinkByToken($token)
    {
        $data = explode(':', $token);

        if (count($data) < 2) {
            return;
        }

        return self::where('id', $data[0])
                    ->where('available_at', '>=', Carbon::now())
                    ->where(function ($query) {
                        $query
                            ->whereNull('max_visits')
                            ->orWhereRaw('max_visits > num_visits');
                    })
                    ->where('token', $data[1])
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
