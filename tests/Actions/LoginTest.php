<?php

namespace MagicLink\Test\Actions;

use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\User;

class LoginTest extends TestCase
{
    public function test_auth()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');

        $this->assertAuthenticatedAs(User::first());
    }
}
