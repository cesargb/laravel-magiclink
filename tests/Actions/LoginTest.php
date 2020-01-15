<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\Login;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Cesargb\MagicLink\Test\User;

class LoginTest extends TestCase
{
    public function test_auth()
    {
        $magiclink = MagicLink::create(new Login(User::first()));

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');

        $this->assertNotNull(auth()->user());

        $this->assertEquals(auth()->user()->id, User::first()->id);
    }

    public function test_auth_and_redirect_to_other_link()
    {
        $magiclink = MagicLink::create(new Login(User::first(), '/test'));

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/test');

        $this->assertNotNull(auth()->user());

        $this->assertEquals(auth()->user()->id, User::first()->id);
    }
}
