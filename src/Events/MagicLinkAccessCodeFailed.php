<?php

namespace MagicLink\Events;

class MagicLinkAccessCodeFailed
{
    public $magiclink;

    public function __construct($magiclink)
    {
        $this->magiclink = $magiclink;
    }
}
