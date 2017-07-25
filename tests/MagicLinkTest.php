<?php

namespace Cesargb\MagicLink\Test;
use Cesargb\MagicLink\MagicLink;

class MagicLinkTest extends TestCase
{
    /** @test **/
    public function it_can_create_magiclink()
    {
        $this->assertContains('/magiclink/',$this->testUser->create_magiclink(5));
    }

    /** @test **/
    public function it_can_get_magiclink()
    {
        $this->testUser->create_magiclink(5);
        $this->assertEquals($this->testUser->magiclinks()->count(), 1);
    }

    /** @test **/
    public function it_can_delete_all_magiclink()
    {
        $magiclink=new MagicLink();
        $magiclink->delete_all();
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_can_delete_expired_magiclink()
    {
        $magiclink=new MagicLink();
        $magiclink->delete_expired();
        $this->assertTrue(true);
    }

    /** @test **/
    public function it_can_get_magiclink2()
    {
        $url=$this->testUser->create_magiclink(5);
        $response = $this->get($url);
        $response->assertStatus(302);
    }

    /** @test **/
    public function url_error_return_403()
    {
        $response = $this->get('magiclink/error');
        $response->assertStatus(403);
    }
}
