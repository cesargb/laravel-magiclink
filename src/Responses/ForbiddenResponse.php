<?php

namespace MagicLink\Responses;

class ForbiddenResponse
{
    public function __invoke()
    {
        return response('forbidden', 403);
    }
}
