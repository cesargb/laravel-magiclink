<?php

namespace MagicLink\Test;

use Illuminate\Support\Facades\Event;
use MagicLink\Actions\ResponseAction;
use MagicLink\Events\MagicLinkAccessCodeFailed;
use MagicLink\MagicLink;

class AccessCodeThrottleTest extends TestCase
{
    private function createProtectedMagicLink(string $accessCode = '1234'): MagicLink
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode($accessCode);

        return $magiclink;
    }

    public function test_locked_after_max_attempts_with_429_and_retry_after()
    {
        $magiclink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 5; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }

        $response = $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(429)
            ->assertCookieMissing('magic-link-access-code');

        $this->assertTrue($response->headers->has('Retry-After'));
    }

    public function test_locked_with_previously_published_config_without_throttle_keys()
    {
        // An app that published config/magiclink.php before these keys existed.
        config(['magiclink.access_code' => ['view' => 'magiclink::ask-for-access-code-form']]);

        $magiclink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 5; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }

        $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(429)
            ->assertCookieMissing('magic-link-access-code');
    }

    public function test_lock_is_scoped_per_magic_link()
    {
        $lockedLink = $this->createProtectedMagicLink();
        $otherLink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 5; $i++) {
            $this->get("{$lockedLink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }

        $this->get("{$lockedLink->url}?access-code=1234")
            ->assertStatus(429);

        // A different magic link, guessed from the same client, is unaffected.
        $this->get("{$otherLink->url}?access-code=wrong")
            ->assertStatus(403);

        $this->get("{$otherLink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertCookie('magic-link-access-code');
    }

    public function test_successful_guess_clears_the_attempt_counter()
    {
        $magiclink = $this->createProtectedMagicLink();

        $this->get("{$magiclink->url}?access-code=wrong-1")->assertStatus(403);
        $this->get("{$magiclink->url}?access-code=wrong-2")->assertStatus(403);
        $this->get("{$magiclink->url}?access-code=wrong-3")->assertStatus(403);

        $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertCookie('magic-link-access-code');

        for ($i = 0; $i < 5; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-again-{$i}")
                ->assertStatus(403);
        }
    }

    public function test_limiter_disabled_when_max_attempts_is_zero()
    {
        config(['magiclink.access_code.max_attempts' => 0]);

        $magiclink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 20; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }
    }

    public function test_limiter_disabled_when_max_attempts_is_none()
    {
        config(['magiclink.access_code.max_attempts' => 'none']);

        $magiclink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 20; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }
    }

    public function test_visiting_without_access_code_does_not_consume_attempts()
    {
        $magiclink = $this->createProtectedMagicLink();

        for ($i = 0; $i < 10; $i++) {
            $this->get($magiclink->url)->assertStatus(403);
        }

        $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertCookie('magic-link-access-code');
    }

    public function test_stale_cookie_does_not_consume_attempts()
    {
        $magiclink = $this->createProtectedMagicLink();

        $this->withCookie('magic-link-access-code', 'not-a-valid-cookie');

        for ($i = 0; $i < 10; $i++) {
            $this->get($magiclink->url)->assertStatus(403);
        }

        $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertCookie('magic-link-access-code');
    }

    public function test_valid_cookie_bypasses_a_locked_link()
    {
        $magiclink = $this->createProtectedMagicLink();

        $response = $this->get("{$magiclink->url}?access-code=1234")
            ->assertCookie('magic-link-access-code')
            ->assertStatus(302);

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === 'magic-link-access-code');

        for ($i = 0; $i < 5; $i++) {
            $this->get("{$magiclink->url}?access-code=wrong-{$i}")
                ->assertStatus(403);
        }
        $this->get("{$magiclink->url}?access-code=wrong-final")
            ->assertStatus(429);

        $this->disableCookieEncryption()->withCookie($cookie->getName(), $cookie->getvalue())
            ->get($magiclink->url)
            ->assertStatus(200)
            ->assertSeeText('the big secret');
    }

    public function test_event_dispatched_only_on_wrong_access_code()
    {
        Event::fake([
            MagicLinkAccessCodeFailed::class,
        ]);

        $magiclink = $this->createProtectedMagicLink();

        $this->get($magiclink->url);
        $this->get("{$magiclink->url}?access-code=wrong");
        $this->get("{$magiclink->url}?access-code=1234");

        Event::assertDispatchedTimes(MagicLinkAccessCodeFailed::class, 1);
    }
}
