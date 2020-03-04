<?php

namespace MagicLink\Test;

use Illuminate\Support\Facades\App;
use MagicLink\MagicLink;

class FeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadRoutes();
    }

    public function test_create_login()
    {
        $this->get('/create/login')
            ->assertStatus(200);

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get(MagicLink::first()->url)
                ->assertStatus(302);
        } else {
            $this->get(MagicLink::first()->url)
                ->assertStatus(302)
                ->assertRedirect('/');
        }
    }

    public function test_create_response_redirect()
    {
        $this->get('/create/redirect?redirectTo=/test')
            ->assertStatus(200);

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get(MagicLink::first()->url)
                ->assertStatus(302)
                ->assertRedirect(url('/test'));
        } else {
            $this->get(MagicLink::first()->url)
                ->assertStatus(302)
                ->assertRedirect('/test');
        }
    }

    public function test_create_response_redirect_to_301()
    {
        $this->get('/create/redirect?redirectTo=/test&status=301')
            ->assertStatus(200);

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get(MagicLink::first()->url)
                ->assertStatus(301)
                ->assertRedirect(url('/test'));
        } else {
            $this->get(MagicLink::first()->url)
            ->assertStatus(301)
            ->assertRedirect('/test');
        }
    }

    public function test_create_response_view_withdata()
    {
        $this->post('/create/view', [
            'view' => 'view',
            'data' => ['text' => 'Lorem, ipsum dolor.'],
        ])->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(200)
            ->assertSeeText('Lorem, ipsum dolor.');
    }

    public function test_create_response_callable()
    {
        $this->get('/create/callback')->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(200)
            ->assertSeeText(MagicLink::skip(1)->first()->url);

        $this->assertEquals(2, MagicLink::count());
    }

    public function test_create_response_download()
    {
        $this->get('/create/download')->assertStatus(200);

        if (preg_match('/5\.5\.*/', App::version())) {
            $this->get(MagicLink::first()->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename="text.txt"');
        } else {
            $this->get(MagicLink::first()->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=text.txt');
        }
    }
}
