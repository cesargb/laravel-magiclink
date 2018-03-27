<?php

namespace Cesargb\MagicLink;

use Cesargb\MagicLink\Models\MagicLink as MagicLinkModel;

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

        $MagicLink->token = str_random(config('magiclink.token.length', 64));

        $MagicLink->available_at = \Carbon\Carbon::now()->addMinute(
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

        $MagicLink = MagicLinkModel::find($data[0]);
        if (!$MagicLink) {
            return false;
        }
        $MagicLink->where('available_at', '>=', \Carbon\Carbon::now())
                    ->where('token', $data[1])
                    ->first();

        if ($MagicLink) {
            $user = config('auth.providers.users.model')::find($MagicLink->user_id);
            if ($user) {
                app()->make('auth')->loginUsingId($MagicLink->user_id);
                if ($MagicLink->redirect_url !== null && $MagicLink->redirect_url != '') {
                    return $MagicLink->redirect_url;
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
        MagicLinkModel::where('available_at', '<', \Carbon\Carbon::now())->delete();
    }
}
