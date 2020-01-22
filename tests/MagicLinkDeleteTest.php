<?php

namespace Cesargb\MagicLink\Test;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\MagicLink;

class MagicLinkDeleteTest extends TestCase
{
    public function test_delete_magiclink_when_is_expired()
    {
        MagicLink::create(new LoginAction(User::first()));

        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $this->assertEquals(2, MagicLink::count());

        MagicLink::deleteMagicLinkExpired();

        $this->assertEquals(1, MagicLink::count());
    }

    public function test_delete_magiclink_when_max_visits_is_completed()
    {
        MagicLink::create(new LoginAction(User::first()));

        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->num_visits = 2;
        $magiclink->max_visits = 2;
        $magiclink->save();

        $this->assertEquals(2, MagicLink::count());

        MagicLink::deleteMagicLinkExpired();

        $this->assertEquals(1, MagicLink::count());
    }

    public function test_delete_magiclink_when_is_expired_after_create()
    {
        MagicLink::create(new LoginAction(User::first()));

        $magiclink = MagicLink::create(new LoginAction(User::first()));

        $magiclink->available_at = now()->subMinute();
        $magiclink->save();

        $this->assertEquals(2, MagicLink::count());

        MagicLink::create(new LoginAction(User::first()));

        $this->assertEquals(2, MagicLink::count());
    }

    public function test_not_expired_magiclink_when_lifetime_is_null()
    {
        MagicLink::create(new LoginAction(User::first()), null);

        $this->assertEquals(1, MagicLink::count());

        MagicLink::deleteMagicLinkExpired();

        $this->assertEquals(1, MagicLink::count());
    }

    public function test_delete_all_magiclink()
    {
        MagicLink::create(new LoginAction(User::first()));

        MagicLink::create(new LoginAction(User::first()));

        $this->assertEquals(2, MagicLink::count());

        MagicLink::deleteAllMagicLink();

        $this->assertEquals(0, MagicLink::count());
    }
}
