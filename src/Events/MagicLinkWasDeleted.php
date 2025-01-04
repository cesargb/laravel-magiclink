<?php

namespace MagicLink\Events;

class MagicLinkWasDeleted
{
    public $magiclink;

    public function __construct($magiclink)
    {
        $this->magiclink = $magiclink;
    }
}
