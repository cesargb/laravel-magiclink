[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)
[![Build Status](https://travis-ci.org/cesargb/laravel-magiclink.svg?branch=2.x)](https://travis-ci.org/cesargb/laravel-magiclink)
[![StyleCI](https://github.styleci.io/repos/98337902/shield?branch=2.x)](https://github.styleci.io/repos/98337902)
[![Total Downloads](https://img.shields.io/packagist/dt/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)


# Introducción

Mediante la clase `MagicLink` podemos crear un enlace seguro que tras
ser visitado conellvará acciones determinadas, que nos permitirá
ofrecer contenido seguro e incluso hacer login en la aplicación.

## Instalación

Puedes instalarlo via composer:

```bash
composer require cesargb/laravel-magiclink
```
Una vez instalado necesitas realizar un migrate a tu base de datos.

```bash
php artisan migrate
```

## Primer ejemplo

Con este ejemplo puedes crear un enlace para hacer auto login en tu
aplicación con el usuario deseado:

```php
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

$urlToAutoLogin =  MagicLink::create(new LoginAction($user))->url
```

## Generar un MagicLink

La clase `MagicLink` dispone del método `create` para generar una clase
que mediante la propiedad `url` obtendremos el enlace que enviaremos a
nuestro visitante.

Este método require de una acción a realizar.

## Acciones

Cada MagicLink está asociado a una acción, que es la que se realizará
una vez se visite el enlace.

* [LoginAction](#loginaction)
* [ResponseAction](#responseaction)

### LoginAction

Está acción permite logarse en la aplicación mediante el enlace generado
por `MagicLink`.

Su constructor admite el usuario que hará login. Opcionalmente podemos
especificarle la [respuesta HTTP](https://laravel.com/docs/master/responses)
mediante el argumento `$httpResponse` y especificar el `$guard`.

Ejemplos:

```php
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

// Sample 1; Login and redirect to dash board
$urlToDashBoard = MagicLink::create(
    new LoginAction(User::first(), redirec('/dashboard'))
)->url;

// Sample 2; Login and view forms to password reset and use guard web
$urlShowView = MagicLink::create(
    new LoginAction(
        User::first(),
        view('password.reset', ['email' => 'user@example.tld'])
    )
)->url;

// Sample 3; Login in other guard and redirect default
$urlShowView = MagicLink::create(
    new LoginAction(
        User::first(),
        null,
        'otherguard'
    )
)->url;
```

### ResponseAction

Está acción nos permite acceder a contenido privado sin necesidad
de realizar login. Su constructor acepta como argumento la
[respuesta HTTP](https://laravel.com/docs/master/responses)

Ejemplos:

```php
use MagicLink\Actions\ResponseAction;
use MagicLink\MagicLink;

$urlToViewContenct = MagicLink::create(
    new ResponseAction(
        view('promotion.code', ['code' => 'YOUR_CODE'])
    )
)->url;

$urlToDownLoadFile = MagicLink::create(
    new ResponseAction(function () {
        return Storage::download('private/docs.pdf');
    })
);

$urlToCustomFunction = MagicLink::create(
    new ResponseAction(function () {
        Auth::login(User::first());

        return redirect('/change_password');
    })
);
```

## Tiempo de vida del enlace MagicLink

Por defecto un enlace estará disponible durante 24 horas despues de su
creación. Podemos modificar el tiempo de vida en minutos del enlace, mediante
la opción `$timelife` disponible en el método `create`. Este argummento acepta
el valor `null` para que no expire en el tiempo.

```php
$lifetime = 60; // 60 minutes

$magiclink = MagicLink::create(new ResponseAction(), 60);
```



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
