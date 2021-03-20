<?php

use Illuminate\Support\Facades\Route;
use MagicLink\Middlewares\MagiclinkMiddleware;

Route::group(
    [
        'middleware' => [
            MagiclinkMiddleware::class,
            'web',
        ],
    ],
    function () {
        Route::get(
            config('magiclink.url.validate_path', 'magiclink').'/{token}',
            'MagicLink\Controllers\MagicLinkController@access'
        );
    }
);
