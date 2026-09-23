<?php

use MagicLink\Middlewares\MagiclinkMiddleware;
use MagicLink\Responses\Response;

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

        /*
        |--------------------------------------------------------------------------
        | Access Code Max Attempts
        |--------------------------------------------------------------------------
        |
        | Here you may specify how many wrong access code guesses are allowed for
        | a single magic link before it is locked for a while. The limit is keyed
        | per magic link, not per IP, so it cannot be bypassed by rotating IPs.
        |
        | Set to 0 or 'none' to disable this limiter.
        |
        */
        'max_attempts' => env('MAGICLINK_ACCESS_CODE_MAX_ATTEMPTS', 5),

        /*
        |--------------------------------------------------------------------------
        | Access Code Decay Seconds
        |--------------------------------------------------------------------------
        |
        | Here you may specify how many seconds a magic link stays locked after
        | reaching the max attempts above.
        |
        */
        'decay_seconds' => env('MAGICLINK_ACCESS_CODE_DECAY_SECONDS', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delete Expired Magic Links When Creating New Ones
    |--------------------------------------------------------------------------
    |
    | When creating a new MagicLink, the system will automatically delete
    | expired magic links from the database. If you want to disable this
    | automatic cleanup, set this value to false.
    |
    */
    'delete_expired_when_created' => env('MAGICLINK_DELETE_EXPIRED_WHEN_CREATED', false),

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
        'class' => Response::class,
    ],

    'middlewares' => [
        'throttle:magiclink',
        MagiclinkMiddleware::class,
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

    /*
    |--------------------------------------------------------------------------
    | Allowed Classes for Deserialization
    |--------------------------------------------------------------------------
    |
    | When your custom actions contain object properties (e.g., Eloquent models),
    | you must explicitly allow those classes to be deserialized for security.
    |
    | Example: If your action has a User property, add: User::class
    |
    | Environment variable: comma-separated class names
    | MAGICLINK_ALLOWED_CLASSES="App\Models\User,App\Models\Post"
    |
    */
    'allowed_classes' => env('MAGICLINK_ALLOWED_CLASSES')
        ? array_filter(array_map('trim', explode(',', env('MAGICLINK_ALLOWED_CLASSES', ''))))
        : [],
];
