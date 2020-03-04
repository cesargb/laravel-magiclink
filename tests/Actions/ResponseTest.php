<?php

namespace MagicLink\Test\Actions;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\User;

class ResponseTest extends TestCase
{
    public function test_response_null()
    {
        $magiclink = MagicLink::create(new ResponseAction());

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get($magiclink->url)
                ->assertStatus(302);
        } else {
            $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect(config('magiclink.url.redirect_default', '/'));
        }
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

    public function test_response_redirect()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(redirect('/test'))
        );

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/test');
    }

    public function test_response_view()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(
                view('view', ['text' => 'Lorem, ipsum dolor.'])
            )
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem, ipsum dolor.');
    }

    public function test_response_string()
    {
        $magiclink = MagicLink::create(
            new ResponseAction('Lorem ipsum dolor sit')
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem ipsum dolor sit');
    }

    public function test_response_json()
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

    public function test_response_callable_download()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(function () {
                return Storage::download('text.txt');
            })
        );

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename="text.txt"');
        } else {
            $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=text.txt');
        }
    }

    public function test_response_callable_login()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(function () {
                Auth::login(User::first());

                return redirect('/change_password');
            })
        );

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/change_password');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_response_callable_view()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(function () {
                return view('view', ['user' => User::first()]);
            })
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Email: '.User::first()->email);
    }
}
