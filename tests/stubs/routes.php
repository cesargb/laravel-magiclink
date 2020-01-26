<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use MagicLink\Actions\LoginAction;
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;
use MagicLink\Test\User;

Route::get('/create/login', function () {
    return MagicLink::create(new LoginAction(User::first()))->url;
});

Route::get('/create/redirect', function (Request $request) {
    return MagicLink::create(new ResponseAction(redirect(
        $request->input('redirectTo', '/'),
        $request->input('status', 302)
    )))->url;
});

Route::post('/create/view', function (Request $request) {
    return MagicLink::create(new ResponseAction(
        view($request->input('view', 'view'), $request->input('data', []))
    ))->url;
});

Route::get('/create/callback', function () {
    return MagicLink::create(new ResponseAction(function () {
        return 'a';
    }))->url;
});