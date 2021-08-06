# FriendsOfBabba/Core plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require friendsofbabba/core
bin/cake plugin load FriendsOfBabba/Core
```

**Warn**: sometimes can be necessary to install other dependencies (I've to test it):

```sh
composer require friendsofcake/crud
composer require friendsofcake/search
composer require firebase/php-jwt
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

Disable `CsrfTokenProtectionMiddleware` in `src/Application.php`:

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

# Hooks

You can customize specific application behaviors using `hooks`.
