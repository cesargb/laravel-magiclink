<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\ViewAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;

class ViewTest extends TestCase
{
    public function test_view()
    {
        $magiclink = MagicLink::create(new ViewAction('view'));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('This is a tests view');
    }

    public function test_view_with_data()
    {
        $magiclink = MagicLink::create(
            new ViewAction('data', ['data' => 'Lorem, ipsum dolor.'])
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');
    }
}
