<?php

namespace MagicLink\Test\Actions;

use Illuminate\Support\Facades\Auth;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\CustomAutenticable;
use MagicLink\Test\TestSupport\CustomUserProvider;
use MagicLink\Test\TestSupport\User;

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

    public function test_auth_custom()
    {
        Auth::provider('custom_provider', function ($app, array $config) {
            return new CustomUserProvider();
        });

        config()->set('auth.providers.custom', [
            'driver' => 'custom_provider',
        ]);

        config()->set('auth.guards.web.provider', 'custom');

        $magiclink = MagicLink::create(new LoginAction(new CustomAutenticable('user_1')));

        $this->get($magiclink->url)
            ->assertStatus(302)
            ->assertRedirect('/');

        $this->assertAuthenticatedAs(new CustomAutenticable('user_1'));
    }
}
