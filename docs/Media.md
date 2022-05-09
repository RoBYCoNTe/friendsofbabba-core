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
    $this->addBehavior(PluginManager::getInstance()->getFQN('Media'), ['media']));
    $this->belongsTo('Media', [
        'className' => PluginManager::getInstance()->getFQN('Media'),
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
    $this->addBehavior(PluginManager::getInstance()->getFQN('Media'), ['media']));
    $this->belongsToMany('MediaCollection', [
        'className' => PluginManager::getInstance()->getFQN('Media'),
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

Done, you can now upload/delete/update media in your restful api.
**In any case**: if you are using `friendsofbabba-ra` remember to
add `fileFields` into `useDataProvider`
during initialization of the client.
