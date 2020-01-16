<?php

namespace Cesargb\MagicLink\Test;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\User;

class MagicLinkTest extends TestCase
{
    public function test_fail_login_when_token_is_bad()
    {
        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $this->get($magiclink->url.'bad')
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_error', '/magiclink/error'));

        $this->assertNull(auth()->user());
    }

    public function test_get_status_403_when_access_to_error_page()
    {
        $this->get(config('magiclink.url.redirect_error', '/magiclink/error'))
                ->assertStatus(403);
    }
}
