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

Generate your private and public key necessary to work with JWT authentication:

```sh
openssl genrsa -out config/jwt.key 1024
openssl rsa -in config/jwt.key -outform PEM -pubout -out config/jwt.pem
chown -R www-data:www-data config/
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

To complete installation you have to execute this command:

```sh
bin/cake install fob
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

## Language

The plugin provides a set of language files useful to work with client apps like react-admin
(if you want to handle messages using database instead of static files).
The base language file is installed when `bin/cake install` command is executed.

You can do many things with cli:

- `bin/cake langauge export`: generate a new language file based on data
  saved in to the database.
- `bin/cake language import`: import data from existing file (placed in
  root folder of the app).
- `bin/cake language clear_cache`: clear cached language files to recreate it, this
  command is useful when you change localized messages inside the database.

## Permission

The permission modules allow you to define list of permissions necessary to work
inside the application. Permissions are controller's action dependent and are always
generated scanning all controllers and actions configured in your application.

To refresh permissions:

```sh
bin/cake permission scan
```

Regenerate list of role's permissions.

## Media

You can use `MediaBehavior` to instruct your entities to have media field(s).
The basic installation of the plugin automatically configure required tables to handle
media files, you just need to implement `Media` in your entity.

You can use two different type of media:

- `belongsTo` to set a single media file.
- `belongsToMany` to set multiple media files.

### Configure `belongsTo`

Add column referencing media using this code:

```sql
alter table table_name add column media_id integer unsigned not null;
alter table table_name add constraint fk_table_name_media_id foreign key (media_id) references media(id);
```

Open your `Table` file and add the following code to map the media:

```php
...
class TableNameTables extends BaseTable {
	...
	public function initialize(array $config) {
		$this->addBehavior('FriendsOfBabba/Core.Media', ['media']));
		$this->belongsTo('Media', [
			'className' => 'FriendsOfBabba/Core.Media',
			'foreignKey' => 'media_id',
			'joinType' => 'LEFT',
			'propertyName' => 'media'
		]);
	}
	...
}
```

Open your `Entity` file and add a new accessible field:

```php
protected $_accessible = [
  ...
  'media' => true,
  ...
];
```

### Configure `belongsToMany`

Create a _many to many_ relationship table:

```sql
create table table_name_media (
  table_name_id integer unsigned not null,
  media_id integer unsigned not null,
  primary key (table_name_id, media_id),
  foreign key (table_name_id) references table_name(id),
  foreign key (media_id) references media(id)
);
```

Open your `Table` file and add the following code to map the media:

```php
class TableNameTables extends BaseTable {
	...
	public function initialize(array $config) {
		$this->addBehavior('FriendsOfBabba/Core.Media', ['media']));
		$this->belongsToMany('MediaCollection', [
			'className' => 'FriendsOfBabba/Core.Media',
			'foreignKey' => 'media_id',
			'joinType' => 'LEFT',
			'propertyName' => 'media_collection'
		]);
	}
	...
}
```

Open your `Entity` file and add a new accessible field:

```php
protected $_accessible = [
  ...
  'media_collection' => true,
  ...
];
```

## Workflow(s)

Before execute any action you need to create your table.
Table must follow these rules:

- must have created, modified and deleted (this one needs to be nullable) fields (all datetime)
- must have always an id field (integer unsigned not null)
- cannot have fields with these names (reserved for workflow):
  - notes
  - state
  - is_private
  - is_current
  - transaction

To create a new workflow you can use the cli:

```sh
bin/cake workflow create <EntityName> \
  -s <List of states separated by comma> \
  -t <List of routes in from:to state format separated by comma>
```

The command will do many things (that can be executed separately):

- Create entity model, table and filter (`bin/cake entity create <EntityName>`)
- Create entity transactions _database table_ (`bin/cake workflow create_transaction_table <EntityName>`)
- Create entity transactions _cake model, table and filter_ (`bin/cake entity create <EntityName>Transactions`)
- Create workflow files (`bin/cake workflow create_files <EntityName> -s <States> -t <Transitions>`)
- Create entity API controller (`bin/cake api create <EntityName>`)

**Remember**: when creating workflows, the Core will automatically load the configured entity
as resources following the standard path: `api/<EntityName>` using `dasherized` version of the resource
(suppose you've created a workflow for an entity called `ResearchProjects`, you will
access the resource using `api/research-projects`).

## Data Migration

In some cases you needs to move data from one database to another. Suppose you
are implementing a new application and you want to use the data from an old application.

In this case you can organize data migration using predefined scripts.

Using the cli you can create new data migration script:

```sh
bin/cake data-migration create <NameOfMigration>
```

This command will create a new migration script in to `src/Command/DataMigration` directory.
You have to open and edit the script before execute it.

To execute the migration script:

```sh
bin/cake data-migration execute <NameOfMigration>
```
