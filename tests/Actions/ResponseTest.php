<?php

namespace MagicLink\Test\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\User;

class ResponseTest extends TestCase
{
    public function testResponseNull()
    {
        $magiclink = MagicLink::create(new ResponseAction());

        $this->get($magiclink->url)
            ->assertStatus(302)
            ->assertRedirect(config('magiclink.url.redirect_default', '/'));
    }

    public function testResponseCallable()
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

    public function testResponseRedirect()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(redirect('/test'))
        );

        $this->get($magiclink->url)
                ->assertStatus(302)
                ->assertRedirect('/test');
    }

    public function testResponseView()
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

    public function testResponseString()
    {
        $magiclink = MagicLink::create(
            new ResponseAction('Lorem ipsum dolor sit')
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertSeeText('Lorem ipsum dolor sit');
    }

    public function testResponseJson()
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

    public function testResponseCallableDownload()
    {
        $magiclink = MagicLink::create(
            new ResponseAction(function () {
                return Storage::download('text.txt');
            })
        );

        $this->get($magiclink->url)
            ->assertStatus(200)
            ->assertHeader('content-disposition', 'attachment; filename=text.txt');
    }

    public function testResponseCallableLogin()
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

    public function testResponseCallableView()
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

    public function testResponseCallableWithMagicLink()
    {
        $magiclink = MagicLink::create(new ResponseAction(
            function ($magiclink) {
                if (! MagicLink::first()) {
                    return redirect('/');
                }

                $magiclink->delete();

                return 'callback called';
            })
        );

        $this->get($magiclink->url)->assertStatus(200);

        $this->assertNull(MagicLink::first());
    }
}
