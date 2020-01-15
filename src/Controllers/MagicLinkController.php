<?php

namespace Cesargb\MagicLink\Controllers;

use Cesargb\MagicLink\Models\MagicLink;
use Illuminate\Routing\Controller;

class MagicLinkController extends Controller
{
    /**
     * Return error response.
     *
     * @return void
     */
    public function error()
    {
        return response(null, 403);
    }

    /**
     * Return validation redirect.
     */
    public function validate($token)
    {
        $magiclink = MagicLink::getValidMagicLinkByToken($token);

        if (! $magiclink) {
            return redirect(config('magiclink.url.redirect_error', '/magiclink/error'));
        }

        return $magiclink->run();
    }
}
