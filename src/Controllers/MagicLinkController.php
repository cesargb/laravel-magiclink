<?php

namespace Cesargb\MagicLink\Controllers;

use Cesargb\MagicLink\MagicLink;
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
        abort(403);
    }

    /**
     * Return validation redirect.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validate($token)
    {
        $magicLink = new MagicLink;

        $result = $magicLink->auth($token);

        if ($result == false) {
            return redirect(config('magiclink.url.redirect_error', '/magiclink/error'));
        } else {
            return redirect($result);
        }
    }
}
