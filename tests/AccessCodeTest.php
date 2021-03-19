<?php

namespace MagicLink\Test;

use Illuminate\Support\Facades\Hash;
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

        $magiclink->access_code = Hash::make('1234');
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(403)
                ->assertViewIs('magiclink::ask-for-access-code-form');
    }

    public function test_sucessfull_if_provide_access_code()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'the big secret';
        }));

        $magiclink->access_code = Hash::make('1234');
        $magiclink->save();

        $response = $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(302)
            ->assertRedirect($magiclink->url);

        $cookie = $response->headers->getCookies()[0];

        $this->disableCookieEncryption()->withCookie($cookie->getName(), $cookie->getvalue())
            ->get($magiclink->url)
            ->assertStatus(200)
            ->assertSeeText('the big secret');
    }
}
