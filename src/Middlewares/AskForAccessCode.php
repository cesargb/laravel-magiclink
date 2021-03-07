<?php

namespace MagicLink\Middlewares;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MagicLink\MagicLink;
use Illuminate\Support\Facades\Hash;

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
        if ($this->isAccessCodeValid($request->route('token'), $request->get('download-plan-access-code'))) {
            // access code is valid
            setcookie('download-plan-access-code', encrypt($request->get('download-plan-access-code')), 0, '/');
            return redirect($request->url());
        }

        try {
            $accessCode = decrypt(Arr::get($_COOKIE, 'download-plan-access-code'));
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
