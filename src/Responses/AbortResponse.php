<?php

namespace MagicLink\Responses;

class AbortResponse implements ResponseContract
{
    public function __invoke($options = [])
    {
        abort(
            $options['status'] ?? 403,
            $options['message'] ?? '',
            $options['headers'] ?? []
        );
    }
}
