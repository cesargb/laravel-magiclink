<?php

namespace MagicLink\Test\Responses;

use MagicLink\Responses\AbortResponse;
use MagicLink\Test\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AbortResponseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->expectException(HttpException::class);

        $this->app['config']->set(
            'magiclink.invalid_response.class',
            AbortResponse::class
        );
    }

    public function test_default_response()
    {
        $this->get('/magiclink/bad_token')
            ->assertStatus(403)
            ->assertSee('Forbidden');
    }

    public function test_custom_response()
    {
        $this->app['config']->set(
            'magiclink.invalid_response.options',
            [
                'message' => 'You Shall Not Pass!',
                'status' => 403,
            ]
        );

        $this->get('/magiclink/bad_token')
            ->assertStatus(403)
            ->assertSee('You Shall Not Pass!');
    }
}
