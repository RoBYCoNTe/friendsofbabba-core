<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class MediaInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$this->dropSchema("media");

		$mediaSchema = new TableSchema('media');
		$mediaSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('user_id', ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => true])
			->addColumn('code', ['type' => 'string', 'length' => 50, 'null' => false])
			->addColumn('filename', ['type' => 'string', 'length' => 255, 'null' => false])
			->addColumn('filetype', ['type' => 'string', 'length' => 255, 'null' => false])
			->addColumn('filesize', ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false])
			->addColumn('filepath', ['type' => 'string', 'length' => 255, 'null' => false])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addColumn('deleted', ['type' => 'datetime', 'null' => true])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			])
			->addConstraint('fk_media_users', [
				'type' => 'foreign',
				'columns' => ['user_id'],
				'references' => ['users', 'id']
			]);

		$this->installSchema($io, $mediaSchema);
	}
}
