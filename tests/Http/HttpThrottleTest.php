<?php

namespace MagicLink\Test\Http;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class HttpThrottleTest extends TestCase
{
    public function test_http_failed_when_rate_limit_is_exceeded()
    {
        config(['magiclink.rate_limit' => 1]);

        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->get($magiclink->url)
            ->assertStatus(200);

        $this->get($magiclink->url)
            ->assertStatus(429);
    }
}
