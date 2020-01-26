<?php

namespace MagicLink\Test;

use MagicLink\MagicLink;

class FeatureTest extends TestCase
{
    public function setUp():void
    {
        parent::setUp();

        $this->loadRoutes();
    }

    public function test_create_login()
    {
        $this->get('/create/login')
            ->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(302)
            ->assertRedirect('/');
    }

    public function test_create_response_redirect()
    {
        $this->get('/create/redirect?redirectTo=/test')
            ->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(302)
            ->assertRedirect('/test');
    }

    public function test_create_response_redirect_to_301()
    {
        $this->get('/create/redirect?redirectTo=/test&status=301')
            ->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(301)
            ->assertRedirect('/test');
    }

    public function test_create_response_view_withdata()
    {
        $this->post('/create/view', [
            'view' => 'data',
            'data' => ['data' => 'Lorem, ipsum dolor.'],
        ])->assertStatus(200);

        $this->get(MagicLink::first()->url)
            ->assertStatus(200)
            ->assertSeeText('Lorem, ipsum dolor.');
    }
}
