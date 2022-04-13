<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class LanguageInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$this->dropSchema("language_messages");
		$this->dropSchema("languages");

		$this->_installLanguageSchema($io);
		$this->_installLanguageMessageSchema($io);
	}

	private function _installLanguageSchema(ConsoleIo $io)
	{
		$languageSchema = new TableSchema('languages');
		$languageSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('code', ['type' => 'string', 'length' => 50, 'null' => false])
			->addColumn('name', ['type' => 'string', 'length' => 50, 'null' => false])
			->addConstraint('pk_languages', ['type' => 'primary', 'columns' => ['id']]);

		$this->installSchema($io, $languageSchema);
	}

	private function _installLanguageMessageSchema(ConsoleIo $io)
	{
		$languageMessageSchema = new TableSchema('language_messages');
		$languageMessageSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('language_id', ['type' => 'integer', 'unsigned' => true, 'null' => false])
			->addColumn('code', ['type' => 'string', 'length' => 250, 'null' => false])
			->addColumn('text', ['type' => 'text', 'null' => false])
			->addConstraint('pk_language_messages', ['type' => 'primary', 'columns' => ['id']])
			->addConstraint('fk_language_messages_languages', [
				'type' => 'foreign',
				'columns' => ['language_id'],
				'references' => ['languages', 'id']
			]);

		$this->installSchema($io, $languageMessageSchema);
	}
}
