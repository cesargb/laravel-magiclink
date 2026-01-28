<?php

namespace MagicLink\Security\Serializable;

use MagicLink\Actions\ActionAbstract;

class ActionSerializable
{
    public static function serialize(ActionAbstract $action): string
    {
        return Serializable::serialize($action);
    }

    public static function unserialize(string $value): ActionAbstract
    {
        return Serializable::unserialize($value);
    }
}