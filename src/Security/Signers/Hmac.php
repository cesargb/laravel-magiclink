<?php

namespace MagicLink\Security\Signers;

use Illuminate\Support\Str;

class Hmac
{
    public static function sign(string $data)
    {
        $key = self::parseKey();

        return base64_encode(hash_hmac('sha256', $data, $key, true));
    }

    public static function verify(string $data, string $hmac): bool
    {
        $expectedHmac = self::sign($data);

        return hash_equals($expectedHmac, $hmac);
    }

    private static function parseKey()
    {
        if (Str::startsWith($key = config('app.key'), $prefix = 'base64:')) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }
}
