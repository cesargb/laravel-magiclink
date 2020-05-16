<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MagicLink\MagicLink;
use MagicLink\Responses\Response;

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
        $responseClass = config('magiclink.invalid_response.class', Response::class);

        $response = new $responseClass;

        return $response(config('magiclink.invalid_response.options', []));
    }
}
