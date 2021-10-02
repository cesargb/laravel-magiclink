<?php

namespace MagicLink;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

trait AccessCode
{
    protected $cookieName = 'magic-link-access-code';

    public function getResponseAccessCode()
    {
        $responseFromForm = $this->getResponseAccessCodeFromForm();

        return $responseFromForm
            ? $responseFromForm
            : $this->getResponseAccessCodeFromCookie();
    }

    /**
     * Check if access code is right.
     */
    private function checkAccessCode(?string $accessCode): bool
    {
        if ($accessCode === null) {
            return false;
        }

        return Hash::check($accessCode, $this->access_code);
    }

    /**
     * The action was protected with an access code.
     */
    private function protectedWithAcessCode(): bool
    {
        return ! is_null($this->access_code ?? null);
    }

    private function getResponseAccessCodeFromForm()
    {
        $accessCode = $this->getAccessCodeFromForm();

        if (
            $this->protectedWithAcessCode()
            && $accessCode
            && $this->checkAccessCode($accessCode)
        ) {
            return redirect(request()->url())->withCookie(
                cookie(
                    $this->cookieName,
                    encrypt($accessCode),
                    0,
                    '/'
                )
            );
        }

        return null;
    }

    private function getResponseAccessCodeFromCookie()
    {
        if ($this->protectedWithAcessCode()) {
            if ($this->getAccessCodeFromCookie()) {
                if ($this->checkAccessCode($this->getAccessCodeFromCookie())) {
                    return null;
                }
            }

            return response(view('magiclink::ask-for-access-code-form'), 403);
        }

        return null;
    }

    private function getAccessCodeFromForm()
    {
        return request()->get('access-code');
    }

    private function getAccessCodeFromCookie()
    {
        $accessCodeCookies = request()->cookie($this->cookieName);

        if (! $accessCodeCookies) {
            return null;
        }

        try {
            $cookie = Arr::last((array) $accessCodeCookies);

            return decrypt($cookie);
        } catch (DecryptException $e) {
            return null;
        }
    }
}
