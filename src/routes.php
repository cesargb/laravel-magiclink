<?php


Route::group(['middleware' => ['web']], function () {
    Route::any(
        config('magiclink.url.redirect_error', 'magiclink/error'), 
        "Cesargb\MagicLink\Controllers\MagicLinkController@error"
    );
    
    Route::any(
        config('magiclink.url.validate_path', 'magiclink').'/{token}', 
        "Cesargb\MagicLink\Controllers\MagicLinkController@validate"
    );
});
