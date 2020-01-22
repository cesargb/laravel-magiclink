<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get(
        config('magiclink.url.validate_path', 'magiclink').'/{token}',
        'MagicLink\Controllers\MagicLinkController@access'
    );
});
