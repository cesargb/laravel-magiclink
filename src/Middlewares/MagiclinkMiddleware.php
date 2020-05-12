<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MagicLink\MagicLink;
use MagicLink\Responses\ForbiddenResponse;

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

        return $this->response();
    }

    protected function response()
    {
        $responseClass = config('response', ForbiddenResponse::class);
        dd($responseClass);
        $response = new $responseClass;

        return $response();
    }
}
