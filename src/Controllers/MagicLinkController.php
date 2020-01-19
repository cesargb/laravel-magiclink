<?php

namespace Cesargb\MagicLink\Controllers;

use Cesargb\MagicLink\Models\MagicLink;
use Illuminate\Routing\Controller;

class MagicLinkController extends Controller
{
    public function access($token)
    {
        $magiclink = MagicLink::getValidMagicLinkByToken($token);

        if (! $magiclink) {
            return config('magiclink.response.error', abort(403));

            return redirect(config('magiclink.url.redirect_error', '/magiclink/error'));
        }

        return $magiclink->run();
    }
}
