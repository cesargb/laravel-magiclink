<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Cesargb\MagicLink\Test\User;

class LoginTest extends TestCase
{
    public function test_auth_null_response()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_auth_with_response_callable()
    {
        $magiclink = MagicLink::create(
            new LoginAction(User::first(),
            function () {
                return 'callback called';
            }
        ));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('callback called');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_auth_with_response_redirect()
    {
        $magiclink = MagicLink::create(
            new LoginAction(User::first(), redirect('/test'))
        );

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/test');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_auth_with_response_view()
    {
        $magiclink = MagicLink::create(
            new LoginAction(
                User::first(),
                view('data', ['data' => 'Lorem, ipsum dolor.'])
            )
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_auth_with_response_string()
    {
        $magiclink = MagicLink::create(
            new LoginAction(
                User::first(),
                'Lorem ipsum dolor sit'
            )
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem ipsum dolor sit');

        $this->assertAuthenticatedAs(User::first());
    }
}
