<?php

namespace MagicLink\Responses;

class ViewResponse implements ResponseContract
{
    public function __invoke($options = [])
    {
        return view(
            $options['view'] ?? null,
            $options['data'] ?? []
        );
    }
}
