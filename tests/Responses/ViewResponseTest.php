<?php

namespace MagicLink\Test\Responses;

use MagicLink\Responses\ViewResponse;
use MagicLink\Test\TestCase;

class ViewResponseTest extends TestCase
{
    public function test_custom_view_response()
    {
        $this->app['config']->set(
            'magiclink.invalid_response.class',
            ViewResponse::class
        );

        $this->app['config']->set(
            'magiclink.invalid_response.options',
            [
                'view' => 'view',
                'data' => ['text' => 'Lorem, ipsum dolor.'],
            ]
        );

        $this->get('/magiclink/bad_token')
            ->assertStatus(200)
            ->assertSeeText('Lorem, ipsum dolor.');
    }
}
