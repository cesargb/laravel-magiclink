<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MagicLink\MagicLink;

class MagiclinkMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');

        $magicLink = MagicLink::getValidMagicLinkByToken($token);

        if ($magicLink) {
            $magicLink->visited();

            return $next($request);
        }

        return response('forbidden', 403);
    }
}
