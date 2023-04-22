<?php

namespace MagicLink\Test;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;

class AccessCodeTest extends TestCase
{
    public function test_sucessfull_if_not_protected_with_access_code()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('the big secret');
    }

    public function test_forbidden_if_protected_with_access_code()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $this->get($magiclink->url)
                ->assertStatus(403)
                ->assertViewIs('magiclink::ask-for-access-code-form');
    }

    public function test_forbidden_if_protected_with_access_code_and_send_bad()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $response = $this->get("{$magiclink->url}?access-code=123")
                ->assertStatus(403)
                ->assertViewIs('magiclink::ask-for-access-code-form');

        $this->assertEquals(0, count($response->headers->getCookies()));
    }

    public function test_forbidden_if_protected_with_access_code_and_send_null()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $response = $this->get("{$magiclink->url}")
                ->assertStatus(403)
                ->assertViewIs('magiclink::ask-for-access-code-form');

        $this->assertEquals(0, count($response->headers->getCookies()));
    }

    public function test_sucessfull_if_provide_access_code()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $response = $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertRedirect($magiclink->url);

        $cookie = $response->headers->getCookies()[0];

        $this->disableCookieEncryption()->withCookie($cookie->getName(), $cookie->getvalue())
            ->get($magiclink->url)
            ->assertStatus(200)
            ->assertSeeText('the big secret');
    }

    public function test_forbidden_if_provide_access_code_of_other_link()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->protectWithAccessCode('1234');

        $response = $this->get("{$magiclink->url}?access-code=1234");

        $cookie = $response->headers->getCookies()[0];

        $magiclinkOther = MagicLink::create(new ResponseAction(function () {
            return 'the other big secret';
        }));

        $magiclinkOther->protectWithAccessCode('1234');

        $this->disableCookieEncryption()->withCookie($cookie->getName(), $cookie->getvalue())
            ->get($magiclinkOther->url)
            ->assertStatus(403);
    }

    public function test_forbidden_if_protected_with_access_code_custmo()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        config(['magiclink.access-code.view' => 'access-code-custom']);

        $magiclink->protectWithAccessCode('1234');

        $this->get($magiclink->url)
                ->assertStatus(403)
                ->assertViewIs('access-code-custom');
    }
}
