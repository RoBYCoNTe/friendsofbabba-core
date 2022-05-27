# FriendsOfBabba/Core plugin for CakePHP 🥧

A rapid development plugin for CakePHP 4.x apps including:

- Basic database schema migrations to enable:
  - users
  - roles and permissions
  - languages (for client app localization)
- Basic authentication using JWT
- Optional authentication using SPID (Italian Identity Provider)
- Basic command line interface to manage data
- Basic workflow factory to create and manage entities workflow
- Basic extender infrastructure to extend FOB core entities and tables
- Basic crud factory that collect and manage crud for every configured entity
- Basic notification system to send emails and push notifications
- Basic media utilities to manage media files
- An so on... 🎁

## Installation

Run the following command to install the plugin:

```sh
composer require friendsofbabba/core
bin/cake plugin load FriendsOfBabba/Core
```

Due to the fact that we will use REST services we can disable CSRF token protection
in the middleware stack. To do so we have to comment the following lines to the `Application.php`
of our newly created app:

```php
// ->add(new CsrfProtectionMiddleware([
//     'httponly' => true,
// ]));
```

In `config/app.php` under `App` configuration section you have to add these lines:

```php
...
'App' => [
	'name' => 'Babbàpp',
	'logo' => false,
	'dashboard' => '/dashboard/index.html#/',
]
...
```

These informations are required to handle email notifications.

Add `FriendsOfBabba/Core/Error/AppExceptionRenderer.php` to `config/app.php`:

```php
'Error' => [
	'exceptionRenderer' => \FriendsOfBabba\Core\Error\AppExceptionRenderer::class,
],
```

## Configure SPID

To configure SPID (if not yet installed) install it at database level:

```sh
bin/cake migrations migrate --plugin FrinedsOfBabba/Core --source Migrations/Spid
```

Extend `UserProfile` entity and table adding new extenders at configuration level in `config/app.php`:

```php
use FriendsOfBabba\Core\Model\Entity\Extender\SpidUserProfileExtender;
use FriendsOfBabba\Core\Model\Table\Extender\SpidUsersTableExtender;
use FriendsOfBabba\Core\Model\Table\Extender\SpidUserProfilesTableExtender;

...
'Model' => [
	'Entity' => [
		'UserProfile' => SpidUserProfileExtender::class
	],
	'Table' => [
		'Users' => SpidUsersTableExtender::class
		'UserProfiles' => SpidUserProfilesTableExtender::class
	]
]
...
```

Configure Google Recaptcha Key that will be used to validate signup forms
after first login adding this line in `config/app.php`:

```php
'Recaptcha' => ['secret' => 'Your Secret']
```

As last step you have to configure SPID authorization config in `config/app.php`:

```php
'Spid' => [
	// Configure simplesaml remote service URL required to receive SPID callbacks.
	'endpoint' => 'https://spid.local/simplesaml/login',
	'service' => 'yourservice',
	'secret' => 'yoursecret',
	'expires' => 300,

	// Table from which to retrieve the user data.
	'table' => 'FriendsOfBabba/Core.Users',

	// Configure SPID access methods and fields to use.
	// Finder matching code for SPID.
	'finder' => 'UserProfiles.fiscal_code',
	// Joins required to retrieve data based on finder.
	'joins' => ['UserProfiles'],
	// List of entity fields to retrieve.
	'contain' => ['UserProfiles', 'Roles'],
	// List of default roles to add for new users.
	'roles' => ['user'],

	// Configure back link to return to the application after SPID login.
	'back' => [
		'client' => "http://localhost:3000/#/login"
	]
],
```

In most cases you will need to modify the first 3 lines of previous code.

**Pay attention to change the `back` value to the correct URL of your application
in different environments.**
