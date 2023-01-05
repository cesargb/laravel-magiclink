<?php

namespace MagicLink\Test\TestSupport;

use Illuminate\Routing\Controller;

class MyController extends Controller
{
    public function __invoke()
    {
        return 'im a controller invoke';
    }

    public function index()
    {
        return 'im a controller index';
    }
}
