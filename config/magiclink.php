<?php


return [

    'access_code' => [
        /*
        |--------------------------------------------------------------------------
        | Access Code View
        |--------------------------------------------------------------------------
        |
        | Here you may specify the view to ask for access code.
        |
        */
        'view' => 'magiclink::ask-for-access-code-form',
    ],

    /*
    |--------------------------------------------------------------------------
    | Delete Magic Link Expired massive
    |--------------------------------------------------------------------------
    |
    | Expired MagicLinks are automatically and massively deleted from the database.
    | If you want to disable this option, change the value to false.
    |
    | If you disable this option, expired MagicLinks will be deleted one by one
    | triggering the event MagicLink\Events\MagicLinkWasDeleted.
    |
    */
    'delete_massive' => env('MAGICLINK_DELETE_MASSIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Disable default route
    |--------------------------------------------------------------------------
    |
    | If you wish use your custom controller, you can invalidate the
    | default route of magic link, mark this configuration as true,
    | and add your custom route with the middleware:
    | MagicLink\Middlewares\MagiclinkMiddleware
    |
    */
    'disable_default_route' => false,

    /*
    |--------------------------------------------------------------------------
    | Response when token is invalid
    |--------------------------------------------------------------------------
    |
    | Here you may specify the class with method __invoke to get the response
    | when token is invalid
    |
    */
    'invalid_response' => [
        'class' => MagicLink\Responses\Response::class,
    ],

    'middlewares' => [
        'throttle:magiclink',
        MagicLink\Middlewares\MagiclinkMiddleware::class,
        'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of attempts to rate limit per minutes
    |
    | Default: none, if you want to enable rate limit, set as integer
    */
    'rate_limit' => env('MAGICLINK_RATE_LIMIT', 'none'),

    'token' => [
        /*
        |--------------------------------------------------------------------------
        | Token size
        |--------------------------------------------------------------------------
        |
        | Here you may specify the length of token to verify the identify.
        | Max value is 255 characters, it will be used if bigger value is set.
        |
        */
        'length' => 64,
    ],

    'url' => [
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
        | Path to Validate Token and Auto Auth
        |--------------------------------------------------------------------------
        |
        | Here you may specify the name of the path you'd like to use so that
        | the verify token and auth in system.
        |
        */
        'validate_path' => 'magiclink',
    ],

];
