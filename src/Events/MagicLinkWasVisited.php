<?php

namespace MagicLink\Events;

class MagicLinkWasVisited
{
    public $magiclink;

    public function __construct($magiclink)
    {
        $this->magiclink = $magiclink;
    }
}
