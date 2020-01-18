<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Cesargb\MagicLink\Test\User;

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
