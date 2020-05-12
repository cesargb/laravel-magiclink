<?php

namespace MagicLink\Test\Stubs\Responses;

class RedirectResponse
{
    public function __invoke()
    {
        return redirect('/ups', 302);
    }
}
