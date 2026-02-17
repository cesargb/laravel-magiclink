<?php

namespace MagicLink\Test\Actions;

use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\CustomAction;
use MagicLink\Test\TestSupport\User;

class CustomTest extends TestCase
{
    public function test_custom_without_allowed_classes()
    {
        $magiclink = MagicLink::create(new CustomAction(User::first()));

        $this->get($magiclink->url)
            ->assertStatus(419)
            ->assertJsonFragment([
                'code' => 'type_error',
            ]);
    }

    public function test_custom_with_allowed_classes()
    {
        config()->set('magiclink.allowed_classes', [User::class]);

        $magiclink = MagicLink::create(new CustomAction(User::first()));

        $this->get($magiclink->url)
            ->assertStatus(200);
    }
}
