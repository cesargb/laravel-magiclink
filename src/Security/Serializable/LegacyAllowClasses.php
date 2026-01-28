<?php

namespace MagicLink\Security\Serializable;

use MagicLink\Actions\ActionAbstract;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class LegacyAllowClasses
{
    public static function get(string $type): array
    {
        $base = [ResponseHeaderBag::class, Response::class];

        if (! class_exists($type)) {
            throw new \RuntimeException('Unsupported serialized type.');
        }

        if (is_subclass_of($type, ActionAbstract::class) || is_subclass_of($type, Response::class)) {
            return array_values(array_unique(array_merge([$type], $base)));
        }

        throw new \RuntimeException('Unsupported serialized type.');
    }
}
