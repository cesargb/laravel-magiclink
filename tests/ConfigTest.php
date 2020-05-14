<?php

namespace MagicLink\Test;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Responses\RedirectResponse;

class ConfigTest extends TestCase
{
    public function test_custom_token_length()
    {
        $this->app['config']->set('magiclink.token.length', 10);

        $url = MagicLink::create(new ResponseAction())->url;

        $this->assertEquals(10, strlen($this->getTokenFromUrl($url)));
    }

    protected function getTokenFromUrl($url)
    {
        $parts = explode(':', $url);

        return end($parts);
    }

    public function test_custom_url_validate_path()
    {
        $this->app['config']->set('magiclink.url.validate_path', 'otherpath');

        $url = MagicLink::create(new ResponseAction())->url;

        $this->assertGreaterThan(0, strpos($url, '/otherpath/'));
    }

    public function test_custom_url_redirect_default()
    {
        $this->app['config']->set('magiclink.url.redirect_default', '/dashboard');

        $url = MagicLink::create(new ResponseAction())->url;

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get($url)
                ->assertStatus(302);
        } else {
            $this->get($url)
                ->assertStatus(302)
                ->assertRedirect('/dashboard');
        }
    }

    public function test_save_action_serialize()
    {
        MagicLink::create(new ResponseAction());

        $action = DB::table('magic_links')->first(['action'])->action;

        if (getenv('DB_DRIVER') === 'pgsql') {
            $this->assertInstanceOf(
                ResponseAction::class,
                unserialize(base64_decode($action))
            );
        } else {
            $this->assertInstanceOf(
                ResponseAction::class,
                unserialize($action)
            );
        }
    }

    public function test_other_response()
    {
        $this->app['config']->set('magiclink.invalid_response.class', RedirectResponse::class);
        $this->app['config']->set('magiclink.invalid_response.options.to', '');

        $url = MagicLink::create(new ResponseAction())->url;

        $this->get($url.'bad')
            ->assertStatus(302);
    }
}
