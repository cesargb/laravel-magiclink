<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MagicLink\MagicLink;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MagiclinkMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');

        if (MagicLink::getValidMagicLinkByToken($token)) {
            return $next($request);
        }

        return new HttpException(403, 'forbidden');
    }
}