<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\Login;
use Cesargb\MagicLink\Actions\View;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Cesargb\MagicLink\Test\User;

class ViewTest extends TestCase
{
    public function test_view()
    {
        $magiclink = MagicLink::create(new View('view'));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('This is a tests view');
    }

    public function test_view_with_data()
    {
        $magiclink = MagicLink::create(
            new View('data', ['data' => 'Lorem, ipsum dolor.'])
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');
    }
}
