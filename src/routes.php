<?php


Route::group(['middleware' => ['web']], function () {
    Route::any(config('magiclink.url.redirect_error', 'magiclink/error'), "Controllers/MagicLinkController@error");
    Route::any(config('magiclink.url.validate_path', 'magiclink').'/{token}', "Controllers/MagicLinkController@validate" );
});
