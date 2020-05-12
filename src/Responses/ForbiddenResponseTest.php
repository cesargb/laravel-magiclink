<?php

namespace MagicLink\Responses;

class ForbiddenResponseTest
{
    public function __invoke()
    {
        return response('forbidden', 402);
    }
}
