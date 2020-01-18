<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\LoginAction;
use Cesargb\MagicLink\Actions\ResponseAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Cesargb\MagicLink\Test\User;

class ResponseTest extends TestCase
{
    public function test_null_response()
    {
        $magiclink = MagicLink::create(new ResponseAction());

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/');
    }

    public function test_response_callable()
    {
        $magiclink = MagicLink::create(new ResponseAction(
            function () {
                return 'callback called';
            })
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('callback called');
    }

    public function test_auth_with_response_redirect()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(redirect('/test'))
        );

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/test');
    }

    public function test_auth_with_response_view()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(
                view('data', ['data' => 'Lorem, ipsum dolor.'])
            )
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');
    }

    public function test_auth_with_response_string()
    {
        $magiclink = MagicLink::create(
            new ResponseAction('Lorem ipsum dolor sit')
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem ipsum dolor sit');
    }

    public function test_auth_with_response_json()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(
                response()->json(['message' => 'json message'], 213)
            )
        );

        $this->get($magiclink->url)
                ->assertStatus(213)
                ->assertJson(['message' => 'json message']);
    }
}
