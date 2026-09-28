<?php

namespace MagicLink\Responses\Concerns;

use MagicLink\Responses\Response;

trait HandlesInvalidResponse
{
    protected function badResponse()
    {
        $responseClass = config('magiclink.invalid_response.class', Response::class);

        $response = new $responseClass;

        return $response(config('magiclink.invalid_response.options', []));
    }
}