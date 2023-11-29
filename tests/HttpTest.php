<?php

namespace MagicLink\Test;

use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;

class HttpTest extends TestCase
{
    public function test_http_get_request()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->num_visits = 4;
        $magiclink->save();

        $this->get($magiclink->url)
            ->assertStatus(200)
            ->assertSeeText('private content');

        $magiclink->refresh();

        $this->assertEquals(5, $magiclink->num_visits);
    }

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

    public function test_http_options_request_has_not_effects()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $magiclink->num_visits = 4;
        $magiclink->save();

        $this->options($magiclink->url)
            ->assertStatus(200)
            ->assertDontSeeText('private content');

        $magiclink->refresh();

        $this->assertEquals(4, $magiclink->num_visits);
    }

    public function test_http_urlencode_legacy()
    {
        $magiclink = MagicLink::create(new ResponseAction(function () {
            return 'private content';
        }));

        $urlLegacy = str_replace(urlencode(':'), ':', $magiclink->url);

        $this->get($urlLegacy)
            ->assertStatus(200)
            ->assertSeeText('private content');
    }
}
