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

        if (MagicLink::getValidMagicLinkByToken($token)) {
            return $next($request);
        }

        return response('forbidden', 403);
    }
}
