<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Http\Request;
use MagicLink\MagicLink;
use MagicLink\Responses\Concerns\HandlesInvalidResponse;

class MagiclinkMiddleware
{
    use HandlesInvalidResponse;

    public const REQUEST_ATTRIBUTE = 'magiclink';

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
            return $responseAccessCode ?: redirect($magicLink->url);
        }

        if ($responseAccessCode) {
            return $responseAccessCode;
        }

        if (! $magicLink->visited()) {
            return $this->badResponse();
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $magicLink);

        return $next($request);
    }
}
