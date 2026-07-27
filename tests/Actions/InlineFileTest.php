<?php

namespace MagicLink\Test\Actions;

use MagicLink\Actions\InlineFileAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;

class InlineFileTest extends TestCase
{
    public function test_inline_file()
    {
        $magiclink = MagicLink::create(new InlineFileAction('text.txt'));

        $this->get($magiclink->url)
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'inline; filename=text.txt'
            );
    }

    public function test_inline_file_with_custom_name()
    {
        $magiclink = MagicLink::create(
            new InlineFileAction('text.txt', 'other.txt')
        );

        $this->get($magiclink->url)
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'inline; filename=other.txt'
            );
    }

    public function test_inline_file_from_other_disk()
    {
        $magiclink = MagicLink::create(
            (new InlineFileAction('text_alternative.txt'))->disk('alternative')
        );

        $this->get($magiclink->url)
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'inline; filename=text_alternative.txt'
            );
    }
}
