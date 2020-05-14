<?php

namespace MagicLink\Responses;

class RedirectResponse implements ResponseContract
{
    public function __invoke($options = [])
    {
        return redirect(
            $options['to'] ?? null,
            $options['status'] ?? 302,
            $options['headers'] ?? []
        );
    }
}
