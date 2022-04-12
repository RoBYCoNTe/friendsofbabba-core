# FriendsOfBabba/Core plugin for CakePHP 🥧

A rapid development plugin for CakePHP.

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require friendsofbabba/core
bin/cake plugin load FriendsOfBabba/Core
```

Add it to your `src/Application.php` file:

```php
public function boostrap() : void
{
    // Stuff
    // Load more plugin here
    $this->addPlugin("FriendsOfBabba/Core", ['routes' => true]);
}
```

FriendsOfBabba/Core uses RESTFul API to provide access to the whole set of functionalities
exposed in to the library. We can disable `CsrfTokenProtectionMiddleware`
in `src/Application.php` because not necessary:

```php
        // ->add(new CsrfProtectionMiddleware([
        //     'httponly' => true,
        // ]));
```

Generate your private and public key necessary to work with JWT authentication:

```sh

openssl genrsa -out config/jwt.key 1024
openssl rsa -in config/jwt.key -outform PEM -pubout -out config/jwt.pem

```

Add `FriendsOfBabba/Core/Error/AppExceptionRenderer.php` to `config/app.php`:

```php
'Error' => [
    'exceptionRenderer' => 'FriendsOfBabba/Core/Error/AppExceptionRenderer',
],
```

To complete installation you have to execute this command:

```sh
bin/cake install
```

## API

### Hooks

You can customize specific application behaviors using `hooks`.

### Language

TODO: Explain how language service work.

### Permission

TODO: Explain how permission work.

### Data Migration

TODO: Explain how data migration work.
