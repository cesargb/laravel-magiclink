<?php


Route::group(['middleware' => ['web']], function () {
    Route::any(config('magiclink.url.redirect_error', 'magiclink/error'), function () {
        abort(403);
    });

    Route::any(config('magiclink.url.validate_path', 'magiclink').'/{token}', function ($token) {
        $MagicLink = new \Cesargb\MagicLink\MagicLink();
        $result = $MagicLink->auth($token);
        if ($result == false) {
            return redirect(config('magiclink.url.redirect_error', '/magiclink/error'));
        } else {
            return redirect($result);
        }
    });
});
