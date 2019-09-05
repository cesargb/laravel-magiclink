<?php

namespace Cesargb\MagicLink\Test;

use Carbon\Carbon;
use Cesargb\MagicLink\MagicLink;

class MagicLinkTest extends TestCase
{
    public function test_it_can_create_magiclink()
    {
        $this->assertStringContainsString('/magiclink/', $this->testUser->create_magiclink(5));
    }

    public function test_it_can_get_magiclink()
    {
        $this->testUser->create_magiclink();

        $this->assertEquals($this->testUser->magiclinks()->count(), 1);
    }

    public function test_it_can_delete_all_magiclink()
    {
        $this->testUser->create_magiclink();

        $magiclink = new MagicLink();
        $magiclink->delete_all();

        $this->assertEquals($this->testUser->magiclinks()->count(), 0);
    }

    public function test_it_can_delete_expired_magiclink()
    {
        $magiclink = new MagicLink();

        $this->testUser->create_magiclink();
        $this->testUser->create_magiclink();

        $link1 = $this->testUser->magiclinks()->first();
        $link1->available_at = Carbon::yesterday();
        $link1->save();

        $magiclink->delete_expired();

        $this->assertEquals($this->testUser->magiclinks()->count(), 1);
    }

    public function test_url_error_return_403()
    {
        $response = $this->get('magiclink/error');
        $response->assertStatus(403);
    }

    public function test_auth_with_magiclink()
    {
        $url = $this->testUser->create_magiclink();

        $response = $this->get($url);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertEquals(auth()->user()->id, $this->testUser->id);
    }

    public function test_auth_with_magiclink_and_custom_redirect()
    {
        $url = $this->testUser->create_magiclink('/test');

        $response = $this->get($url);

        $response->assertStatus(302);
        $response->assertRedirect('/test');
        $this->assertEquals(auth()->user()->id, $this->testUser->id);
    }

    public function test_fail_with_badlink()
    {
        $url = $this->testUser->create_magiclink();

        $response = $this->get($url.'bad_token');

        $this->assertNull(auth()->user());
        $response->assertStatus(302);
        $response->assertRedirect(config('magiclink.url.redirect_error', 'magiclink/error'));
    }

    public function test_fail_with_token_expired()
    {
        $url = $this->testUser->create_magiclink();

        $link1 = $this->testUser->magiclinks()->first();
        $link1->available_at = Carbon::yesterday();
        $link1->save();

        $response = $this->get($url);

        $this->assertNull(auth()->user());
        $response->assertStatus(302);
        $response->assertRedirect(config('magiclink.url.redirect_error', 'magiclink/error'));
    }
}
