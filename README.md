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

## Language

The plugin provides a set of language files useful to work with react-admin.
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
generated scanning the controllers.

To refresh permissions:

```sh
bin/cake permission scan
```

Regenerate list of role's permissions.

## Workflow(s)

Before execute any action you need to create your table. Table must follow
this rules:

- must have created, modified and deleted fields (datetime)
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

## CRUD

Every created entity is subjected to create, read, update and delete actions.

You can override, for your custom table, the crud descriptors.

## Media

You can use `MediaBehavior` to instruct your entities to have media field(s).
To use this behavior your have to install `media` table executing this command:

```sh
bin/cake install db --filter media`
```

After you have to implement `Media` in your entity. You can use two different type:

- `belongsTo` to set a single media file.
- `belongsToMany` to set multiple media files.

### Configure `belongsTo`

Add column referencing media using this code:

```sql
alter table table_name add column media_id integer unsigned not null;
alter table table_name add constraint fk_table_name_media_id foreign key (media_id) references media(id);
```

Open your entity `Table` file and add the following code to map the media:

```php
use FriendsOfBabba\Core\PluginManager;

class TableNameTables extends BaseTable {

...other code...

  public function initialize(array $config) {
    $this->addBehavior(PluginManager::instance()->getFQN('Media'), ['media']));
    $this->belongsTo('Media', [
        'className' => PluginManager::instance()->getFQN('Media'),
        'foreignKey' => 'media_id',
        'joinType' => 'LEFT',
        'propertyName' => 'media'
    ]);
  }

  ...other code...

}
```

Open your entity's `Entity` file and add a new accessible field:

```php
protected $_accessible = [
  // ...
  'media' => true,
];
```

### Configure `belongsToMany`

Create many-to-many relationship table:

```sql
create table table_name_media (
  table_name_id integer unsigned not null,
  media_id integer unsigned not null,
  primary key (table_name_id, media_id),
  foreign key (table_name_id) references table_name(id),
  foreign key (media_id) references media(id)
);
```

Open your entity `Table` file and add the following code to map the media:

```php
use FriendsOfBabba\Core\PluginManager;

class TableNameTables extends BaseTable {

...other code...

  public function initialize(array $config) {
    $this->addBehavior(PluginManager::instance()->getFQN('Media'), ['media']));
    $this->belongsToMany('MediaCollection', [
        'className' => PluginManager::instance()->getFQN('Media'),
        'foreignKey' => 'media_id',
        'joinType' => 'LEFT',
        'propertyName' => 'media_collection'
    ]);
  }

  ...other code...

}
```

Open your entity's `Entity` file and add a new accessible field:

```php
protected $_accessible = [
  // ...
  'media_collection' => true,
];
```

---

Done, you can now upload/delete/update media in your restful api.
**In any case**: if you are using `friendsofbabba-ra` remember to
add `fileFields` into `useDataProvider`
during initialization of the client.
