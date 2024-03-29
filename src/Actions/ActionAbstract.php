<?php

namespace MagicLink\Actions;

abstract class ActionAbstract
{
    /**
     * Execute Action.
     */
    abstract public function run();

    protected $magiclinkId;

    public function setMagiclinkId(string $magiclinkId): self
    {
        $this->magiclinkId = $magiclinkId;

        return $this;
    }
}
