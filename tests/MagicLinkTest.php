<?php

namespace Cesargb\MagicLink\Test;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\Models\MagicLink;

class MagicLinkTest extends TestCase
{
    public function test_create_magiclink_with_lifetime()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()), 60);

        $this->assertLessThanOrEqual(
            1,
            now()->addHour()->diffInSeconds($magiclink->available_at),
        );
    }

    public function test_create_magiclink_with_max_visits()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()), null, 1);

        $this->assertEquals(1, $magiclink->max_visits);
        $this->assertEquals(0, $magiclink->num_visits);
    }

    public function test_fail_login_when_token_is_bad()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $this->get($magiclink->url.'bad')
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_error', '/magiclink/error'));
    }

    public function test_fail_login_when_token_is_bad_defined()
    {
        $this->get('/magiclink/badtoken')
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_error', '/magiclink/error'));
    }

    public function test_get_status_403_when_access_to_error_page()
    {
        $this->get(config('magiclink.url.redirect_error', '/magiclink/error'))
                ->assertStatus(403);
    }

    public function test_redirect_to_403_when_date_is_expired()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_error', '/magiclink/error'));
    }

    public function test_redirect_to_403_when_max_visits_completed()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->max_visits = 2;
        $magiclink->num_visits = 2;
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_error', '/magiclink/error'));
    }

    public function test_ok_when_max_visits_is_minor_num_visits()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->max_visits = 2;
        $magiclink->num_visits = 1;
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');
    }

    public function test_ok_when_max_visits_is_null()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->max_visits = null;
        $magiclink->num_visits = 4;
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');
    }

    public function test_increment_num_visits()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->num_visits = 4;
        $magiclink->save();

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');

        $magiclink->refresh();

        $this->assertEquals(5, $magiclink->num_visits);
    }

    public function test_increment_num_visits_exceeded()
    {
    }
}
