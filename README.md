# FriendsOfBabba/Core plugin for CakePHP 🥧

A rapid development plugin for CakePHP.
This plugin is a collection of tools and helpers to make your development faster.
I've to says thankyou to [FriendsOfCake](https://github.com/FriendsOfCake) for
the great work with their library that I've used as a base for this plugin.

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

## API

### Hooks

You can customize specific application behaviors using `hooks`.
This documentation will be updated when new hooks will be added to the library.

### Language

The plugin provides a set of language files useful to work with react-admin.
The base language file is installed when `bin/cake install` command is executed.

You can do many things with cli:

- `bin/cake langauge export`: generate a new language file based on data
  saved in to the database.
- `bin/cake language import`: import data from existing file (placed in
  root folder of the app).
- `bin/cake language clear_cache`: clear cached language files to recreate it, this
  command is useful when you change localized messages inside the database.

### Permission

The permission modules allow you to define list of permissions necessary to work
inside the application. Permissions are controller's action dependent and are always
generated scanning the controllers.

To refresh permissions:

```sh
bin/cake permission scan
```

Regenerate new list of permissions.
