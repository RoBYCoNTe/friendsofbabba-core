## CRUD

Every created entity is subjected to create, read, update and delete actions.

You can override, for your custom table, the crud descriptors.

### Export

Every model can be exported. Default export is XLSX (thanks to phpoffice/spreadsheets).
You can write your own exporter or customize the default one `CrudExcelDocument`:

```php
public function getGrid(?User $user) : ?Grid {
  $grid = parent::getGrid($user);
  $exporter = $grid->getExporter('xlsx');
  // Do your own customizations.
}
```
