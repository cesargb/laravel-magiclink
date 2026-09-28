<?php

namespace MagicLink\Test\Http;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class ControllerWithoutMiddlewareTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('magiclink.middlewares', ['web']);
    }

    public function test_valid_magiclink_is_not_executed_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->get($magiclink->url)
            ->assertStatus(403)
            ->assertDontSeeText('private content');

        $magiclink->refresh();

        $this->assertEquals(0, $magiclink->num_visits);
    }

    public function test_expired_magiclink_is_not_executed_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $this->get($magiclink->url)
            ->assertStatus(403)
            ->assertDontSeeText('private content');
    }

    public function test_exhausted_magiclink_is_not_executed_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }), null, 1);

        $magiclink->num_visits = 1;
        $magiclink->save();

        $this->get($magiclink->url)
            ->assertStatus(403)
            ->assertDontSeeText('private content');
    }

    public function test_access_code_protected_magiclink_is_not_executed_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->protectWithAccessCode('1234');

        $this->get("{$magiclink->url}?access-code=1234")
            ->assertStatus(403)
            ->assertDontSeeText('private content');
    }

    public function test_unknown_token_returns_invalid_response_without_middleware()
    {
        $this->get('/magiclink/999:invalid-token')
            ->assertStatus(403);
    }

    public function test_post_is_not_executed_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->post($magiclink->url)
            ->assertStatus(403)
            ->assertDontSeeText('private content');
    }
}
