<?php

namespace MagicLink\Events;

class MagicLinkWasCreated
{
    public $magiclink;

    public function __construct($magiclink)
    {
        $this->magiclink = $magiclink;
    }
}