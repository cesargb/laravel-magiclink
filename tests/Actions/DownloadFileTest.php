<?php

namespace MagicLink\Test\Actions;

use MagicLink\Actions\DownloadFileAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class DownloadFileTest extends TestCase
{
    public function test_download_file()
    {
        $magiclink = MagicLink::create(new DownloadFileAction('text.txt'));

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader(
                    'content-disposition',
                    'attachment; filename=text.txt'
                );
    }

    public function test_download_file_with_custom_name()
    {
        $magiclink = MagicLink::create(
            new DownloadFileAction('text.txt', 'other.txt')
        );

        $this->get($magiclink->url)
                ->assertStatus(200)
                ->assertHeader(
                    'content-disposition',
                    'attachment; filename=other.txt'
                );
    }
}
