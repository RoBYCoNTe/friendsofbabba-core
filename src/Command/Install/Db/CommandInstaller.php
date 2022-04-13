<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class CommandInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$this->dropSchema("command_log_rows");
		$this->dropSchema("command_logs");
		$this->dropSchema("commands");

		$this->_installCommandSchema($io);
		$this->_installCommandLogSchema($io);
		$this->_installCommandLogRowSchema($io);
	}

	private function _installCommandSchema(ConsoleIo $io)
	{
		$commandSchema = new TableSchema('commands');
		$commandSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('user_id', ['type' => 'integer', 'unsigned' => true, 'null' => true])
			->addColumn('name', ['type' => 'string', 'length' => 100, 'null' => false])
			->addColumn('args', ['type' => 'string', 'length' => 255, 'null' => true])
			->addColumn('executed_at', ['type' => 'datetime', 'null' => true])
			->addColumn('status', ['type' => 'string', 'length' => 250, 'null' => true])
			->addColumn('result', ['type' => 'text', 'null' => true])
			->addColumn('notify_args', ['type' => 'text', 'null' => true])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addConstraint('pk_commands', ['type' => 'primary', 'columns' => ['id']])
			->addConstraint('fk_commands_users', ['type' => 'foreign', 'columns' => ['user_id'], 'references' => ['users', 'id']]);
		$this->installSchema($io, $commandSchema);
	}

	private function _installCommandLogSchema(ConsoleIo $io)
	{
		$commandLogSchema = new TableSchema('command_logs');
		$commandLogSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('command', ['type' => 'text'])
			->addColumn('created', ['type' => 'datetime'])
			->addColumn('modified', ['type' => 'datetime'])
			->addConstraint('pk_command_logs', ['type' => 'primary', 'columns' => ['id']]);

		$this->installSchema($io, $commandLogSchema);
	}

	private function _installCommandLogRowSchema(ConsoleIo $io)
	{
		$commandLogRowSchema = new TableSchema('command_log_rows');
		$commandLogRowSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('command_log_id', ['type' => 'integer', 'unsigned' => true, 'null' => false])
			->addColumn('output', ['type' => 'text', 'null' => true])
			->addColumn('type', ['type' => 'string', 'length' => 255, 'null' => true])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addConstraint('pk_command_log_rows', ['type' => 'primary', 'columns' => ['id']]);
		$this->installSchema($io, $commandLogRowSchema);
	}
}
