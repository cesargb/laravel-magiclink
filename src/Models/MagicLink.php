<?php

namespace Cesargb\MagicLink\Models;

use Carbon\Carbon;
use Cesargb\MagicLink\Actions\Action;
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
        return url(config('magiclink.url.validate_path', 'magiclink')).'/'.$this->id.':'.$this->token;
    }

    /**
     * Undocumented function.
     *
     * @param int|null $lifetime
     * @return Cesargb\MagicLink\Models\MagicLink;
     */
    public static function create(Action $action, $lifetime = null)
    {
        self::deleteMagicLinkExpired();

        $magiclink = new self();

        $magiclink->token = Str::random(config('magiclink.token.length', 64));

        $magiclink->available_at = Carbon::now()->addMinute(
            $lifetime ?? config('magiclink.token.lifetime', 120)
        );

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
        self::where('available_at', '<', Carbon::now())->delete();
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
