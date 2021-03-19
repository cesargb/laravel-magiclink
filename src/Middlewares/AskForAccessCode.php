<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MagicLink\MagicLink;

class AskForAccessCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $magicLink = MagicLink::getValidMagicLinkByToken($request->route('token'));

        if (!$magicLink || is_null($magicLink->access_code ?? null)) {
            return $next($request);
        }

        if ($this->isAccessCodeValid($request->route('token'), $request->get('download-plan-access-code'))) {
            // access code is valid
            return redirect($request->url())->withCookie(
                cookie(
                    'magic-link-access-code',
                    encrypt($request->get('download-plan-access-code')),
                    0,
                    '/'
                )
            );
        }

        try {
            $accessCode = decrypt($request->cookie('magic-link-access-code'));

            // Validate access_code
            if ($this->isAccessCodeValid($request->route('token'), $accessCode)) {
                return $next($request);
            }
        } catch (DecryptException $e) {
            // empty value in cookie
        }

        return response(view('magiclink::ask-for-access-code-form'), 403);
    }

    private function isAccessCodeValid(string $token, ?string $accessCode): bool
    {
        if ($accessCode === null) {
            return false;
        }

        $magicLink = MagicLink::getValidMagicLinkByToken($token);

        return Hash::check($accessCode, $magicLink->access_code);
    }
}
