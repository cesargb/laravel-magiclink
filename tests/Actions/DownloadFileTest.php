<?php

namespace Cesargb\MagicLink\Test\Actions;

use Cesargb\MagicLink\Actions\DownloadFileAction;
use Cesargb\MagicLink\Models\MagicLink;
use Cesargb\MagicLink\Test\TestCase;

class DownloadFileTest extends TestCase
{
    public function test_download_file()
    {
        $magiclink = MagicLink::create(new DownloadFileAction('text.txt'));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=text.txt');
    }

    public function test_download_file_with_customname()
    {
        $magiclink = MagicLink::create(new DownloadFileAction('text.txt', 'other.txt'));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader('content-disposition', 'attachment; filename=other.txt');
    }
}
