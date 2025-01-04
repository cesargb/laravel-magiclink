<?php

namespace MagicLink\Responses;

class ViewResponse implements ResponseContract
{
    public function __invoke($options = [])
    {
        return response()->view(
            $options['view'] ?? null,
            $options['data'] ?? []
        );
    }
}
