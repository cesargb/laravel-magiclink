<?php

namespace MagicLink\Test\Http;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class HttpHeadTest extends TestCase
{
    public function test_http_head_request_has_not_effects()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->num_visits = 4;
        $magiclink->save();

        $this->head($magiclink->url)
            ->assertStatus(200)
            ->assertDontSeeText('private content');

        $magiclink->refresh();

        $this->assertEquals(4, $magiclink->num_visits);
    }

    public function test_http_head_request_without_valid_magiclink()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $this->head($magiclink->url . '-bad')
            ->assertStatus(404);
    }
}
