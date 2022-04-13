<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class VersionInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$this->dropSchema("versions");
		$versionSchema = new TableSchema('versions');
		$versionSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('code', [
				'type' => 'string',
				'length' => 10,
				'null' => false,
				'default' => '1.0.0'
			])
			->addColumn('created', [
				'type' => 'datetime',
				'null' => false
			])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			]);
		$this->installSchema($io, $versionSchema);
	}
}
