<?php

namespace MagicLink;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use MagicLink\Events\MagicLinkAccessCodeFailed;

trait AccessCode
{
    abstract protected function getAccessCode();

    abstract protected function getMagikLinkId();

    protected $cookieName = 'magic-link-access-code';

    public function getResponseAccessCode()
    {
        if (! $this->protectedWithAccessCode()) {
            return null;
        }

        if ($this->checkAccessCode($this->getAccessCodeFromCookie())) {
            return null;
        }

        $key = $this->accessCodeThrottleKey();
        $maxAttempts = $this->accessCodeMaxAttempts();

        if ($maxAttempts > 0 && RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $secondsRemaining = RateLimiter::availableIn($key);

            return $this->accessCodeFormResponse(429, ['secondsRemaining' => $secondsRemaining])
                ->header('Retry-After', $secondsRemaining);
        }

        $accessCode = $this->getAccessCodeFromForm();

        if ($accessCode !== null && $accessCode !== '') {
            if ($this->checkAccessCode($accessCode)) {
                RateLimiter::clear($key);

                return redirect(request()->url())->withCookie(
                    cookie(
                        $this->cookieName,
                        encrypt($this->getMagikLinkId().'|'.$accessCode),
                        0,
                        '/'
                    )
                );
            }

            if ($maxAttempts > 0) {
                RateLimiter::hit($key, $this->accessCodeDecaySeconds());
            }

            Event::dispatch(new MagicLinkAccessCodeFailed($this));
        }

        return $this->accessCodeFormResponse(403);
    }

    /**
     * Check if access code is right.
     */
    private function checkAccessCode(?string $accessCode): bool
    {
        if ($accessCode === null) {
            return false;
        }

        return Hash::check($accessCode, $this->getAccessCode());
    }

    /**
     * The action was protected with an access code.
     */
    private function protectedWithAccessCode(): bool
    {
        return ! is_null($this->getAccessCode() ?? null);
    }

    private function accessCodeThrottleKey(): string
    {
        return 'magiclink-access-code:'.$this->getMagikLinkId();
    }

    private function accessCodeMaxAttempts(): int
    {
        $maxAttempts = config('magiclink.access_code.max_attempts', 5);

        if ($maxAttempts === 'none' || $maxAttempts === null) {
            return 0;
        }

        return max(0, (int) $maxAttempts);
    }

    private function accessCodeDecaySeconds(): int
    {
        return (int) config('magiclink.access_code.decay_seconds', 300);
    }

    private function accessCodeView(): string
    {
        return config('magiclink.access-code.view')
            ?? config('magiclink.access_code.view', 'magiclink::ask-for-access-code-form');
    }

    private function accessCodeFormResponse(int $status, array $data = [])
    {
        return response()->view($this->accessCodeView(), $data, $status);
    }

    private function getAccessCodeFromForm()
    {
        return request()->input('access-code');
    }

    private function getAccessCodeFromCookie()
    {
        $accessCodeCookies = request()->cookie($this->cookieName);

        if (! $accessCodeCookies) {
            return null;
        }

        try {
            $cookie = Arr::last((array) $accessCodeCookies);

            [$magiclinkId, $accessCode] = explode('|', decrypt($cookie));

            if ($magiclinkId === $this->getMagikLinkId()) {
                return $accessCode;
            }
        } catch (DecryptException $e) {
            return null;
        }
    }
}
