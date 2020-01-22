<?php

namespace MagicLink\Controllers;

use Illuminate\Routing\Controller;
use MagicLink\MagicLink;

class MagicLinkController extends Controller
{
    public function access($token)
    {
        $magiclink = MagicLink::getValidMagicLinkByToken($token);

        if (! $magiclink) {
            return config('magiclink.response.error', response('forbidden', 403));
        }

        return $magiclink->run();
    }
}
