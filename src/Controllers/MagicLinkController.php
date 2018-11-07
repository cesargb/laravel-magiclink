<?php

namespace Cesargb\MagicLink\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MagicLinkController extends Controller
{
    /**
     * Return error response.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function error()
    {
        return abort(403);
    }

    /**
     * Return validation redirect.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validate($token)
    {
        $magicLink = new \Cesargb\MagicLink\MagicLink();

        $result = $magicLink->auth($token);

        if ($result == false) {
            return redirect(config('magiclink.url.redirect_error', '/magiclink/error'));
        } else {
            return redirect($result);
        }
    }
}
