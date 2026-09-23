<?php

namespace MagicLink\Test;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;

class AccessCodePostTest extends TestCase
{
    public function test_post_with_correct_access_code_sets_cookie_and_redirects()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $this->post($magiclink->url, ['access-code' => '1234'])
            ->assertStatus(302)
            ->assertRedirect($magiclink->url)
            ->assertCookie('magic-link-access-code');
    }

    public function test_post_with_wrong_access_code_is_forbidden()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $this->post($magiclink->url, ['access-code' => 'wrong'])
            ->assertStatus(403)
            ->assertViewIs('magiclink::ask-for-access-code-form')  // @phpstan-ignore argument.type
            ->assertCookieMissing('magic-link-access-code');
    }

    public function test_post_with_valid_cookie_redirects_instead_of_419()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $response = $this->get("{$magiclink->url}?access-code=1234")
            ->assertCookie('magic-link-access-code');

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === 'magic-link-access-code');

        $this->disableCookieEncryption()->withCookie($cookie->getName(), $cookie->getvalue())
            ->post($magiclink->url)
            ->assertStatus(302)
            ->assertRedirect($magiclink->url);
    }

    public function test_post_to_unprotected_link_redirects_instead_of_419()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $this->post($magiclink->url)
            ->assertStatus(302)
            ->assertRedirect($magiclink->url);
    }
}
