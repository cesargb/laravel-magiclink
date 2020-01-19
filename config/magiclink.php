<?php

return [

    'token'           => [
        /*
        |--------------------------------------------------------------------------
        | Token size
        |--------------------------------------------------------------------------
        |
        | Here you may specifiy the length of token to verify the identify.
        |
        */
        'length' => 64,
    ],

    'url' => [
        /*
        |--------------------------------------------------------------------------
        | Path to Validate Token and Auto Auth
        |--------------------------------------------------------------------------
        |
        | Here you may specify the name of the path you'd like to use so that
        | the verify token and auth in system.
        |
        */
        'validate_path' => 'magiclink',
        /*
        |--------------------------------------------------------------------------
        | Path default to redirect
        |--------------------------------------------------------------------------
        |
        | Here you may specify the name of the path you'd like to use so that
        | the redirect when verify correct token.
        |
        */
        'redirect_default' => '/',
        /*
        |--------------------------------------------------------------------------
        | Path default to redirect when token is invalid
        |--------------------------------------------------------------------------
        |
        | Here you may specify the name of the path you'd like to use so that
        | the redirect when token is invalid.
        |
        */
        'redirect_error' => 'magiclink/error',
    ],

    'response' => [
        'error' => abort(403),
    ],
];
