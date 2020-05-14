<?php

namespace MagicLink\Responses;

class Response implements ResponseContract
{
    public function __invoke($options = [])
    {
        return response(
            $options['content'] ?? 'forbidden',
            $options['status'] ?? 403,
            $options['headers'] ?? []
        );
    }
}
