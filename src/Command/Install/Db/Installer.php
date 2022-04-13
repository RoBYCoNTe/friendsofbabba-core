<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;

abstract class Installer
{
	public abstract function install(ConsoleIo $io): void;

	public function installSchema(ConsoleIo $io, TableSchema $tableSchema)
	{
		$io->verbose(sprintf('Installing %s table...', $tableSchema->name()));
		$db = ConnectionManager::get('default');

		$queries = $tableSchema->createSql($db);
		foreach ($queries as $sql) {
			$db->execute($sql);
		}
	}

	public function dropSchema(string $tableName)
	{
		$db = ConnectionManager::get('default');
		$db->execute(sprintf("DROP TABLE IF EXISTS %s", $tableName));
	}
}
