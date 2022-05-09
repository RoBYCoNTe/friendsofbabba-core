## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require friendsofbabba/core
bin/cake plugin load FriendsOfBabba/Core
```

```php
        // ->add(new CsrfProtectionMiddleware([
        //     'httponly' => true,
        // ]));
```

Generate your private and public key necessary to work with JWT authentication:

```sh

openssl genrsa -out config/jwt.key 1024
openssl rsa -in config/jwt.key -outform PEM -pubout -out config/jwt.pem
chown -R www-data:www-data config/

```

Add `FriendsOfBabba/Core/Error/AppExceptionRenderer.php` to `config/app.php`:

```php
  'Error' => [
      'exceptionRenderer' => \FriendsOfBabba\Core\Error\AppExceptionRenderer::class,
  ],
```

To complete installation you have to execute this command:

```sh
bin/cake install
```

**Remember**: after first installation is always suggested to create a first migration
that will contains every table and columns that you will need. Nexts updateds
will be more easy to manage.
