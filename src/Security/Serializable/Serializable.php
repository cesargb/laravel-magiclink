<?php

namespace MagicLink\Security\Serializable;

use Laravel\SerializableClosure\SerializableClosure;
use MagicLink\Actions\ActionAbstract;
use MagicLink\Security\Signers\Hmac;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Serializable
{
    public static function serialize($action): string
    {
        $type = match (true) {
            is_string($action) => 'string',
            is_array($action) => 'array',
            $action instanceof SerializableClosure => 'closure',
            default => get_class($action),
        };

        $value = match($type) {
            'string' => $action,
            'array' => json_encode($action),
            default => serialize($action),
        };

        return json_encode([
            'type' => $type,
            'value' => $value,
            'signed' => Hmac::sign($value.$type),
        ]);
    }

    public static function unserialize(string $value)
    {
        try {
            $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid serialized data format.', 0, $e);
        }

        $storedHmac = $data['signed'] ?? '';
        $type = $data['type'] ?? 'string';
        $serializedData = $data['value'] ?? '';

        if (! Hmac::verify($serializedData.$type, $storedHmac)) {
            throw new \RuntimeException('HMAC validation failed. Data may have been tampered with.');
        }

        return match ($type) {
            'string' => $serializedData,
            'array' => json_decode($serializedData, true),
            'closure' => unserialize($serializedData)->getClosure(),
            default => unserialize($serializedData, [
                'allowed_classes' => self::allowlistedClasses($type),
            ]),
        };
    }

    private static function allowlistedClasses(string $type): array
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
