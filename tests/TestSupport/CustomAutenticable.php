<?php

namespace MagicLink\Test\TestSupport;

use Illuminate\Contracts\Auth\Authenticatable;


class CustomAutenticable implements Authenticatable
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getAuthIdentifierName()
    {

    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {

    }

    public function getRememberToken()
    {

    }

    public function setRememberToken($value)
    {

    }

    public function getRememberTokenName()
    {

    }
}
