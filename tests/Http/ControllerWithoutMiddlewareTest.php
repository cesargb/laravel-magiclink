<?php

namespace MagicLink\Test\Http;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

/**
 * Covers the 2.x compatibility path in MagicLinkController::access(): when a request reaches
 * the controller without a MagicLink already resolved by MagiclinkMiddleware (a custom route,
 * a replaced or extended middleware...), the controller falls back to looking the token up
 * itself instead of rejecting the request outright. That fallback is deprecated and will be
 * removed in 3.0, where every request will require MagiclinkMiddleware to have run.
 */
class ControllerWithoutMiddlewareTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('magiclink.middlewares', ['web']);
    }

    private function withDeprecationsCaptured(callable $callback): array
    {
        $triggered = [];

        // set_error_handler invokes the callback with ($errno, $errstr, ...); only $errstr is needed here.
        set_error_handler(function (int $errno, string $errstr) use (&$triggered): bool {
            $triggered[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $triggered;
    }

    public function test_valid_magiclink_still_runs_without_middleware_but_triggers_a_deprecation()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $triggered = $this->withDeprecationsCaptured(function () use ($magiclink) {
            $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('private content');
        });

        $this->assertNotEmpty($triggered);
        $this->assertStringContainsString('MagiclinkMiddleware', $triggered[0]);
    }

    public function test_expired_magiclink_is_rejected_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $this->withDeprecationsCaptured(function () use ($magiclink) {
            $this->get($magiclink->url)
                ->assertStatus(403)
                ->assertDontSeeText('private content');
        });
    }

    public function test_unknown_token_returns_invalid_response_without_middleware()
    {
        $this->withDeprecationsCaptured(function () {
            $this->get('/magiclink/999:invalid-token')
                ->assertStatus(403);
        });
    }

    // The following cases still run the action without the middleware, exactly like the
    // pre-2.29 controller did: max_visits and the access code are only enforced by
    // MagiclinkMiddleware. This gap is closed in 3.0, where these requests will be rejected.

    public function test_exhausted_magiclink_still_runs_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }), null, 1);

        $magiclink->num_visits = 1;
        $magiclink->save();

        $this->withDeprecationsCaptured(function () use ($magiclink) {
            $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('private content');
        });
    }

    public function test_access_code_protected_magiclink_still_runs_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->protectWithAccessCode('1234');

        $this->withDeprecationsCaptured(function () use ($magiclink) {
            $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('private content');
        });
    }

    public function test_post_still_runs_without_middleware()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->withDeprecationsCaptured(function () use ($magiclink) {
            $this->post($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('private content');
        });
    }
}
