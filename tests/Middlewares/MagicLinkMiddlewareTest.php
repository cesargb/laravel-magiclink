<?php

namespace MagicLink\Test\Responses;

use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\User;

class MagicLinkMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_head_request_returns_404_when_magiclink_is_invalid()
    {
        $this->head('/magiclink/bad_token')
            ->assertStatus(404);
    }

    public function test_head_request_returns_200_when_magiclink_is_valid()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $this->head($magiclink->url)
            ->assertStatus(200);
    }
}