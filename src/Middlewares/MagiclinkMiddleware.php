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
        $token = (string) $request->route('token');

        $magicLink = MagicLink::getValidMagicLinkByToken($token);

        if ($request->method() === 'HEAD') {
            return response()->noContent($magicLink ? 200 : 404);
        }

        if (! $magicLink) {
            return $this->badResponse();
        }

        $responseAccessCode = $magicLink->getResponseAccessCode();

        if ($request->isMethod('POST')) {
            return $responseAccessCode ?: redirect($request->url());
        }

        if ($responseAccessCode) {
            return $responseAccessCode;
        }

        if (! $magicLink->visited()) {
            return $this->badResponse();
        }

        return $next($request);
    }

    protected function badResponse()
    {
        $responseClass = config('magiclink.invalid_response.class', Response::class);

        $response = new $responseClass;

        return $response(config('magiclink.invalid_response.options', []));
    }
}
