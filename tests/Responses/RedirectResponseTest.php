<?php

namespace MagicLink\Test\Responses;

use MagicLink\Responses\RedirectResponse;
use MagicLink\Test\TestCase;

class RedirectResponseTest extends TestCase
{
    public function test_custom_redirect_response()
    {
        $this->app['config']->set(
            'magiclink.invalid_response.class',
            RedirectResponse::class
        );

        $this->app['config']->set(
            'magiclink.invalid_response.options',
            [
                'to' => '/go_away',
                'status' => 301,
            ]
        );

        $this->get('/magiclink/bad_token')
            ->assertStatus(301)
            ->assertRedirect('/go_away');
    }
}
