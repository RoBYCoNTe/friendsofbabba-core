### Configure SPID

During first installation you can use the `spid` plugin to create a SPID table.
If you have not installed the plugin yet, you can do it executing this command:

```sh
bin/cake migrations migrate --plugin FrinedsOfBabba/Core --source Migrations/Spid
```

This command will create required fields to work with SPID.
After that you have to register an extender to handle SPID fields in `app.php`:

```php
...
  'Model' => [
    'Entity' => [
      'UserProfile' => [\FriendsOfBabba\Core\Model\Entity\Extender\SpidExtender::class],
    ]
  ]
...
```

- Enable Recaptcha adding the following code to your `app.php` file:
  ```php
  'Recaptcha' => ['secret' => 'Your Secret']
  ```
