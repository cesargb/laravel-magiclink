<?php

namespace Cesargb\MagicLink;

use Carbon\Carbon;
use Cesargb\MagicLink\Models\MagicLink as MagicLinkModel;
use Illuminate\Support\Str;

class MagicLink
{
    public function __construct()
    {
        $this->delete_expired();
    }

    public function add($user, $redirect_url = '', $lifetime = 0)
    {
        $MagicLink = new MagicLinkModel();
        if (is_int($user)) {
            $MagicLink->user_id = $user;
        } else {
            $MagicLink->user_id = $user->id;
        }

        $MagicLink->token = Str::random(config('magiclink.token.length', 64));

        $MagicLink->available_at = Carbon::now()->addMinute(
            ((int) $lifetime > 0 ? $lifetime : config('magiclink.token.lifetime', 120))
        );
        if ($redirect_url != '') {
            $MagicLink->redirect_url = $redirect_url;
        } else {
            $MagicLink->redirect_url = config('magiclink.url.redirect_default', '/');
        }
        $MagicLink->save();

        return url(config('magiclink.url.validate_path', 'magiclink')).'/'.$MagicLink->id.':'.$MagicLink->token;
    }

    public function auth($token)
    {
        $data = explode(':', $token);

        if (count($data) < 2) {
            return false;
        }

        $magicLink = MagicLinkModel::where('id', $data[0])
                    ->where('available_at', '>=', Carbon::now())
                    ->where('token', $data[1])
                    ->first();

        if ($magicLink) {
            $user = config('auth.providers.users.model')::find($magicLink->user_id);

            if ($user) {
                app()->make('auth')->loginUsingId($magicLink->user_id);

                if ($magicLink->redirect_url !== null && $magicLink->redirect_url != '') {
                    return $magicLink->redirect_url;
                }

                return config('magiclink.url.redirect_default');
            }
        }

        return false;
    }

    public function delete_all()
    {
        MagicLinkModel::truncate();
    }

    public function delete_expired()
    {
        MagicLinkModel::where('available_at', '<', Carbon::now())->delete();
    }
}
