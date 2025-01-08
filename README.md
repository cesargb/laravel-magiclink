
# MagicLink for Laravels App

Through the `MagicLink` class we can create a secure link that later
being visited will perform certain actions, which will allow us
offer secure content and even log in to the application.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)
![tests](https://github.com/cesargb/laravel-magiclink/workflows/tests/badge.svg)
[![style-fix](https://github.com/cesargb/laravel-magiclink/actions/workflows/style-fix.yml/badge.svg)](https://github.com/cesargb/laravel-magiclink/actions/workflows/style-fix.yml)
[![Quality Score](https://img.shields.io/scrutinizer/g/cesargb/laravel-magiclink.svg?style=flat-square)](https://scrutinizer-ci.com/g/cesargb/laravel-magiclink)
[![Total Downloads](https://img.shields.io/packagist/dt/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)

## Contents

- [Installation](#installation)
- [Use case](#use-case)
- [Create a MagicLink](#create-a-magiclink)
- [Actions](#actions)
  - [Login](#login-action)
  - [Download file](#download-file-action)
  - [View](#view-action)
  - [Http Response](#http-response-action)
  - [Controller](#controller-action)
  - [Custom Action](#custom-action)
- [Protect with an access code](#protect-with-an-access-code)
- [Lifetime](#lifetime)
- [Events](#events)
- [Customization](#customization)
- [Rate limiting](#rate-limiting)
- [Testing](#testing)
- [Contributing](#contributing)
- [Security](#security)

## Installation

You can install this package via composer using:

```bash
composer require cesargb/laravel-magiclink
```

### Preparing the database

You need to publish the migration to create the `magic_links` table:

```bash
php artisan vendor:publish --provider="MagicLink\MagicLinkServiceProvider" --tag="migrations"
```

After that, you need to run migrations.

```bash
php artisan migrate
```

## Use case

With this example you can create a link to auto login on your application with
the desired user:

```php
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

$urlToAutoLogin =  MagicLink::create(new LoginAction($user))->url
```

## Create a MagicLink

The `MagicLink` class has the `create` method to generate a class that through
the `url` property we will obtain the link that we will send to our visitor.

This method requires the action to be performed.

## Actions

Each MagicLink is associated with an action, which is what will be performed
once the link is visited.

- [Login Action](#login-action)
- [Download file Action](#download-file-action)
- [View Action](#view-action)
- [Http Response Action](#http-response-action)
- [Http Response](#http-response-action)
- [Controller](#controller-action)
- [Custom Action](#custom-action)
- [Custom Base URL](#custom-base-url)

### Login Action

Through the `LoginAction` action, you can log in to the application using the
generated link by `MagicLink`.

Your constructor supports the user who will login. Optionally we can specify
the [HTTP response](https://laravel.com/docs/master/responses) using the method
`response` or specify other guard with method `guard`.

Examples:

```php
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

// Sample 1; Login and redirect to dash board
$action = new LoginAction(User::first());
$action->response(redirect('/dashboard'));

$urlToDashBoard = MagicLink::create($action)->url;

// Sample 2; Login and view forms to password reset and use guard web
$action = new LoginAction(User::first());
$action->response(view('password.reset', ['email' => 'user@example.tld']));

$urlShowView = MagicLink::create($action)->url;

// Sample 3; Login in other guard and redirect default
$action = new LoginAction(User::first());
$action->guard('customguard');

$urlShowView = MagicLink::create($action)->url;

// Sample 4; Login and remember me
$action = new LoginAction(User::first());
$action->remember();

$urlShowView = MagicLink::create($action)->url;
```

### Download file Action

This action, `DownloadFileAction`, permit create a link to download a private file.

The constructor require the file path.

Example:

```php
use MagicLink\Actions\DownloadFileAction;
use MagicLink\MagicLink;

// Url to download the file storage_app('private_document.pdf')
$url = MagicLink::create(new DownloadFileAction('private_document.pdf'))->url;

// Download file with other file_name
$action = new DownloadFileAction('private_document.pdf', 'your_document.pdf');
$url = MagicLink::create($action)->url;

// Download file from other disk
$action = new DownloadFileAction('private_document.pdf')->disk('ftp');
$url = MagicLink::create($action)->url;

```

### View Action

With the action `ViewAction`, you can provide access to the view. You can use
in his constructor the same arguments than method `view` of Laravel.

Example:

```php
use MagicLink\Actions\ViewAction;
use MagicLink\MagicLink;

// Url to view a internal.blade.php
$url = MagicLink::create(new ViewAction('internal', [
    'data' => 'Your private custom content',
]))->url;
```

### Http Response Action

Through the `ResponseAction` action we can access private content without need
login. Its constructor accepts as argument the
[HTTP response](https://laravel.com/docs/responses)
which will be the response of the request.

Examples:

```php
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;

$action = new ResponseAction(function () {
    Auth::login(User::first());

    return redirect('/change_password');
});

$urlToCustomFunction = MagicLink::create($action)->url;
```

### Controller Action

`MagicLink` can directly call a controller via the `ControllerAction` action.

The constructor requires one argument, the name of the controller class. With
the second argument can call any controller method, by default it will use the
`__invoke` method.

```php
use MagicLink\Actions\ControllerAction;
use MagicLink\MagicLink;

// Call the method __invoke of the controller
$url = MagicLink::create(new ControllerAction(MyController::class))->url;

// Call the method show of the controller
$url = MagicLink::create(new ControllerAction(MyController::class, 'show'))->url;
```

### Custom Action

You can create your own action class, for them you just need to extend with
`MagicLink\Actions\ActionAbstract`

```php
use MagicLink\Actions\ActionAbstract;

class MyCustomAction extends ActionAbstract
{
    public function __construct(public int $variable)
    {
    }

    public function run()
    {
        // Do something

        return response()->json([
            'success' => true,
            'data' => $this->variable,
        ]);
    }
}
```

You can now generate a Magiclink with the custom action

```php
use MagicLink\MagicLink;

$action = new MyCustomAction('Hello world');

$urlToCustomAction = MagicLink::create($action)->url;
```

### Custom Base URL

To set the base URL for a magic link, you can use the `baseUrl` method. This method ensures that the provided base URL has a trailing slash, making it ready for URL generation.

```php
$magiclink = MagicLink::create($action);

$magiclink->baseUrl("http://example.com");

$urlShowView = $magiclink->url; // http://example.com/magiclink/...
```

## Protect with an access code

Optionally you can protect the resources with an access code.
You can set the access code with method `protectWithAccessCode`
which accepts an argument with the access code.

```php
$magiclink = MagicLink::create(new DownloadFileAction('private_document.pdf'));

$magiclink->protectWithAccessCode('secret');

$urlToSend = $magiclink->url;
```

### Custom view for access code

You can customize the view of the access code form with the config file `magiclink.php`:

```php
'access_code' => [
    'view' => 'magiclink::access-code', // Change with your view
],
```

This is the [default view](/resources/views/ask-for-access-code-form.blade.php)

## Lifetime

By default a link will be available for 72 hours after your creation. We can
modify the life time in minutes of the link by the `$lifetime` option
available in the `create` method. This argument accepts the value `null` so
that it does not expire in time.

```php
$lifetime = 60; // 60 minutes

$magiclink = MagicLink::create(new ResponseAction(), $lifetime);

$urlToSend = $magiclink->url;
```

We also have another option `$numMaxVisits`, with which we can define the
number of times the link can be visited, `null` by default indicates that there
are no visit limits.

```php
$lifetime = null; // not expired in the time
$numMaxVisits = 1; // Only can visit one time

$magiclink = MagicLink::create(new ResponseAction(), $lifetime, $numMaxVisits);

$urlToSend = $magiclink->url;
```

## Events

MagicLink can fires three events:

### MagicLinkWasCreated

Event `MagicLink\Events\MagicLinkWasCreated`

This event is fired when a magic link is created.

### MagicLinkWasVisited

Event `MagicLink\Events\MagicLinkWasVisited`

This event is fired when a magic link is visited.

### MagicLinkWasDeleted

Event `MagicLink\Events\MagicLinkWasDeleted`

This event is fired when you disable mass deletion. Add this line in your
`.env` file to disable mass deletion:

```.env
# Disable mass deletion for enable event MagicLinkWasDeleted
MAGICLINK_DELETE_MASSIVE=false
```

> [!WARNING]
> If you disable mass deletion, the cleanup will be performed one by one.
> If you have many records, this can be an issue.

## Customization

### Config

To custom this package you can publish the config file:

```bash
php artisan vendor:publish --provider="MagicLink\MagicLinkServiceProvider" --tag="config"
```

And edit the file `config/magiclink.php`

### Migrations

To customize the migration files of this package you need to publish the migration files:

```bash
php artisan vendor:publish --provider="MagicLink\MagicLinkServiceProvider" --tag="migrations"
```

You'll find the published files in `database/migrations/*`

### Custom response when magiclink is invalid

When the magicLink is invalid by default the http request return a status 403.
You can custom this response with config `magiclink.invalid_response`.

#### Response

To return a response, use class `MagicLink\Responses\Response::class`
same `response()`, you can send the arguments with options

Example:

```php
    'invalid_response' => [
        'class'   => MagicLink\Responses\Response::class,
        'options' => [
            'content' => 'forbidden',
            'status' => 403,
        ],
    ],
```

#### Abort

To return a exception and let the framework handle the response,
use class `MagicLink\Responses\AbortResponse::class`.
Same `abort()`, you can send the arguments with options.

Example:

```php
    'invalid_response' => [
        'class'   => MagicLink\Responses\AbortResponse::class,
        'options' => [
            'message' => 'You Shall Not Pass!',
            'status' => 403,
        ],
    ],
```

#### Redirect

Define class `MagicLink\Responses\RedirectResponse::class` to
return a `redirect()`

```php
    'invalid_response' => [
        'class'   => MagicLink\Responses\RedirectResponse::class,
        'options' => [
            'to' => '/not_valid_path',
            'status' => 301,
        ],
    ],
```

#### View

Define class `MagicLink\Responses\ViewResponse::class` to
return a `view()`

```php
    'invalid_response' => [
        'class'   => MagicLink\Responses\ViewResponse::class,
        'options' => [
            'view' => 'invalid',
            'data' => [],
        ],
    ],
```

## Rate limiting

You can limit the number of requests per minute for a magic link. To do this, you need to
set the `MAGICLINK_RATE_LIMIT` environment variable to the desired value.

By default, the rate limit is disable with value 'none', but you can set a value
to limit the requests. For example, to limit the requests to 100 per minute, set

```bash
# .env

MAGICLINK_RATE_LIMIT=100
```

## Testing

Run the tests with:

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email cesargb@gmail.com
instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
