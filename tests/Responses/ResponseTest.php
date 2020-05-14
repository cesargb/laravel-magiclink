<?php

namespace MagicLink\Test\Responses;

use MagicLink\Responses\Response;
use MagicLink\Test\TestCase;

class ResponseTest extends TestCase
{
    public function test_default_response()
    {
        $this->app['config']->set(
            'magiclink.invalid_response.class',
            Response::class
        );

        $this->get('/magiclink/bad_token')
            ->assertStatus(403)
            ->assertSee('forbidden');
    }

    public function test_custom_response()
    {
        $this->app['config']->set(
            'magiclink.invalid_response.class',
            Response::class
        );

        $this->app['config']->set(
            'magiclink.invalid_response.options',
            [
                'content' => 'the door is close',
                'status' => 401,
            ]
        );

        $this->get('/magiclink/bad_token')
            ->assertStatus(401)
            ->assertSee('the door is close');
    }
}
