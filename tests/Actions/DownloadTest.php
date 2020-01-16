<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\Download;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;
use Illuminate\Support\Facades\Storage;

class DownloadTest extends TestCase
{
    public function test_download_file()
    {
        $magiclink = MagicLink::create(new Download(
            'text.txt'
        ));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=text.txt');
    }

    public function test_download_file_with_customname()
    {
        $magiclink = MagicLink::create(new Download(
            'text.txt',
            'other.txt'
        ));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=other.txt');
    }
}
