<?php

namespace MagicLink\Test\Actions;

use MagicLink\Actions\ControllerAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\MyController;

class ControllerTest extends TestCase
{
    public function test_controller_invoke()
    {
        $action = new ControllerAction(MyController::class);

        $magiclink = MagicLink::create($action);

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('im a controller invoke');
    }

    public function test_controller_method()
    {
        $action = new ControllerAction(MyController::class, 'index');

        $magiclink = MagicLink::create($action);

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('im a controller index');
    }
}
