<?php

namespace MagicLink\Test\TestSupport;

use MagicLink\Actions\ActionAbstract;

class CustomAction extends ActionAbstract
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function run() {}
}
