<?php

namespace MagicLink\Test\Http;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\MagicLinkServiceProvider;
use MagicLink\Test\TestCase;

class HttpThrottleTest extends TestCase
{
    public function test_http_failed_when_rate_limit_is_exceeded()
    {
        config(['magiclink.rate_limit' => 1]);
        (new MagicLinkServiceProvider($this->app))->boot();

        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->get($magiclink->url)
            ->assertStatus(200);

        $this->get($magiclink->url)

        ->assertStatus(429);
    }

    public function test_http_when_rate_limit_is_none()
    {
        config(['magiclink.rate_limit' => 'none']);
        (new MagicLinkServiceProvider($this->app))->boot();

        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->get($magiclink->url)
            ->assertStatus(200);

        $this->get($magiclink->url)
            ->assertStatus(200);
    }
}
