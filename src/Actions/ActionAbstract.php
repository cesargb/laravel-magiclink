<?php

namespace MagicLink\Actions;

use MagicLink\MagicLink;

abstract class ActionAbstract
{
    /**
     * Execute Action.
     */
    abstract public function run();

    protected MagicLink $magiclink;

    public function setMagiclink(MagicLink $magiclink): self
    {
        $this->magiclink = $magiclink;

        $this->magiclink->save();

        return $this;
    }
}
