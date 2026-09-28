<?php

namespace MagicLink\Test\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Middlewares\MagiclinkMiddleware;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\CustomMagicLinkController;

class CustomControllerTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('magiclink.disable_default_route', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('custom-magiclink/{token}', function (Request $request) {
            $magicLink = $request->attributes->get(MagiclinkMiddleware::REQUEST_ATTRIBUTE);

            abort_unless($magicLink instanceof MagicLink, 403);

            return $magicLink->run();
        })->middleware(MagiclinkMiddleware::class);

        Route::get('custom-magiclink-subclass/{token}', [CustomMagicLinkController::class, 'access'])
            ->middleware(MagiclinkMiddleware::class);
    }

    public function test_custom_controller_runs_a_valid_magiclink_read_from_the_request_attribute()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $token = basename(parse_url($magiclink->url, PHP_URL_PATH));

        $this->get("custom-magiclink/{$token}")
            ->assertStatus(200)
            ->assertSeeText('private content');
    }

    public function test_custom_controller_rejects_an_expired_magiclink()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $token = basename(parse_url($magiclink->url, PHP_URL_PATH));

        $this->get("custom-magiclink/{$token}")
            ->assertStatus(403)
            ->assertDontSeeText('private content');
    }

    public function test_subclass_overriding_access_with_the_original_signature_still_works()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $token = basename(parse_url($magiclink->url, PHP_URL_PATH));

        $this->get("custom-magiclink-subclass/{$token}")
            ->assertStatus(200)
            ->assertSeeText('private content');
    }
}
