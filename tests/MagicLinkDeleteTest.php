<?php

namespace MagicLink\Test;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MagicLink\Actions\LoginAction;
use MagicLink\Actions\ResponseAction;
use MagicLink\Events\MagicLinkWasDeleted;
use MagicLink\MagicLink;
use MagicLink\Test\TestSupport\User;

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

    public function test_delete_all_magiclink_expired_one_and_one_dispatch_event_deleted()
    {
        Event::fake([MagicLinkWasDeleted::class]);

        config(['magiclink.delete_massive' => false]);

        $this->createMagicLinkExpired(3);

        MagicLink::deleteMagicLinkExpired();

        Event::assertDispatched(MagicLinkWasDeleted::class, 3);

        Event::assertDispatched(MagicLinkWasDeleted::class, function (MagicLinkWasDeleted $event) {
            return $event->magiclink->action->run()['message'] === 'Hello World 1';
        });

        $this->assertEquals(0, MagicLink::count());
    }

    public function test_delete_all_magiclink_expired_all_not_dispatch_event_deleted()
    {
        Event::fake([MagicLinkWasDeleted::class]);

        config(['magiclink.delete_massive' => true]);

        $this->createMagicLinkExpired(3);

        MagicLink::deleteMagicLinkExpired();

        Event::assertDispatched(MagicLinkWasDeleted::class, 0);

        $this->assertEquals(0, MagicLink::count());
    }

    private function createMagicLinkExpired(int $count = 1): Collection
    {
        return collect(range(1, $count))
            ->map(function ($index) {
                $magiclink = MagicLink::create(new ResponseAction(['message' => 'Hello World ' . $index]));

                $magiclink->available_at = now()->subMinute();
                $magiclink->save();

                return $magiclink;
            });
    }
}
