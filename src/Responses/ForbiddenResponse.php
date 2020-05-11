<?php

namespace MagicLink\Responses;

class ForbiddenResponse
{
    public static function get()
    {
        return response('forbidden', 403);
    }
}
