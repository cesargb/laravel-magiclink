<?php

namespace MagicLink\Test\Actions;

use MagicLink\Actions\ViewAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\User;

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
            new ViewAction('view', ['text' => 'Lorem, ipsum dolor.'])
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');
    }

    public function test_view_with_object()
    {
        $user = User::first();

        $magiclink = MagicLink::create(
            new ViewAction('view', ['user' => $user])
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Email: '.$user->email);
    }
}
