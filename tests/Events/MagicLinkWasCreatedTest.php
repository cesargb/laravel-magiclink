<?php

namespace MagicLink\Test\Events;

use Illuminate\Support\Facades\Event;
use MagicLink\Actions\ResponseAction;
use MagicLink\Events\MagicLinkWasCreated;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class MagicLinkWasCreatedTest extends TestCase
{
    public function test_event_dispatched_when_link_is_created()
    {
        Event::fake([
            MagicLinkWasCreated::class,
        ]);

        $magiclink = MagicLink::create(new ResponseAction());

        Event::assertDispatched(
            MagicLinkWasCreated::class,
            function (MagicLinkWasCreated $event) use ($magiclink) {
                return $magiclink->id === $event->magiclink->id;
            }
        );
    }
}
