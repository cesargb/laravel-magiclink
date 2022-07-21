<?php

namespace MagicLink\Test\TestSupport;

use Illuminate\Contracts\Auth\Authenticatable;

class CustomAutenticable implements Authenticatable
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getAuthIdentifierName()
    {
        return '';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
        return '';
    }
}
