[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)
[![Build Status](https://travis-ci.org/cesargb/laravel-magiclink.svg?branch=2.x)](https://travis-ci.org/cesargb/laravel-magiclink)
[![StyleCI](https://github.styleci.io/repos/98337902/shield?branch=2.x)](https://github.styleci.io/repos/98337902)
[![Total Downloads](https://img.shields.io/packagist/dt/cesargb/laravel-magiclink.svg?style=flat-square)](https://packagist.org/packages/cesargb/laravel-magiclink)


# Introducción

Este paquete permite crear un enlace seguro con el cual el visitante
podrá acceder a contenido privado e incluso logarse en tu aplicación.

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

Con este ejemplo puedes crear un enlace con el cual el visitante hará
login en tu aplicación con el usuario deseado:

```php
<?php

use App\User;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;


public function getUrlToLogin(User $user): string
{
    $action = new LoginAction($user);

    return MagicLink::create($action)->url
}
```

## Uso

Mediante la clase `MagicLink\MagicLink` podemos crear una
url segura que tras ser visitada está llevará acciones determinadas, que
nos permitirá ofrecer contenido seguro e incluso hacer login en la
aplicación.

Para crear estás URL llamaremos al método `create`, que acepta los siguientes
argumentos:

* `$action`; Será la acción que vamos a llevar a cabo cuando se acceda al enlace.
* `$lifetime`; Tiempo en minutos que el enlace puede usarse desde el momento de
su creación. por defecto 4320 minutos. Si desea que el enlace no caduque puede
definir este argumento como `null`
* `$numMaxVisits`; Número máximo de visitas que el enlace puedes ser accedido,
siendo null por defecto en cuyo caso no hay máximo números de visitas.s

## Acciones

Cada Magic Link está asociado a una acción, que es la que se accionará
cuando visitemos su correspondinete enlace.

### LoginAction

Está acción permite logarse en la aplicación despues de visitar la URL
generada por la clase `MagicLink`

Su constructor admite los siguientes argumentos:

* `$user`; correspondiente al usario con elc ual nos logaremos en la
aplicación.
* `$response`; por defecto nos redirigirá a la página /. Aun
* `$guard`; Guard donde se realizará el login del usaurio.

Ejemplo:

```php
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

// Login and redirect to dash board
$action = new LoginAction(User::first(), redirec('/dashboard'));

$urlToDashBoard = MagicLink::create($action)->url;

// Login and view
$action = new LoginAction(User::first(), view('dashboard'));

$urlShowView = MagicLink::create($action)->url;
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
