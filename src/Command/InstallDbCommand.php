<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Role;

/**
 * InstallDatabase command.
 */
class InstallDbCommand extends Command
{
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser = parent::buildOptionParser($parser);

		return $parser;
	}

	/**
	 * Install tables.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$this->_dropSchema("versions");

		$this->_dropSchema("command_log_rows");
		$this->_dropSchema("command_logs");
		$this->_dropSchema("commands");

		$this->_dropSchema("language_messages");
		$this->_dropSchema("languages");
		$this->_dropSchema("notifications");
		$this->_dropSchema("users_roles");
		$this->_dropSchema("users");

		$this->_dropSchema("role_permissions");
		$this->_dropSchema("roles");

		$this->_installVersionSchema($io);
		$this->_installRoleSchema($io);
		$this->_installRolePermissionSchema($io);

		$this->_installUserSchema($io);
		$this->_installUserRoleSchema($io);
		$this->_installNotificationSchema($io);
		$this->_installLanguageSchema($io);
		$this->_installLanguageMessageSchema($io);

		$this->_installCommandSchema($io);
		$this->_installCommandLogSchema($io);
		$this->_installCommandLogRowSchema($io);

		$this->_insertCommonData();

		$io->success('Installation completed with success!');
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
		$this->_installSchema($io, $commandSchema);
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

		$this->_installSchema($io, $commandLogSchema);
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
		$this->_installSchema($io, $commandLogRowSchema);
	}

	private function _installLanguageSchema(ConsoleIo $io)
	{
		$languageSchema = new TableSchema('languages');
		$languageSchema
			->addColumn('id', ['type' => 'integer', 'unsigned' => true, 'autoIncrement' => true, 'null' => false])
			->addColumn('code', ['type' => 'string', 'length' => 50, 'null' => false])
			->addColumn('name', ['type' => 'string', 'length' => 50, 'null' => false])
			->addConstraint('pk_languages', ['type' => 'primary', 'columns' => ['id']]);

		$this->_installSchema($io, $languageSchema);
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

		$this->_installSchema($io, $languageMessageSchema);
	}

	private function _installNotificationSchema(ConsoleIo $io)
	{
		$notificationSchema = new TableSchema('notifications');
		$notificationSchema
			->addColumn('id', [
				'type' => 'integer',
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('user_id', [
				'type' => 'integer',
				'unsigned' => true,
				'null' => false
			])
			->addColumn('title', [
				'type' => 'string',
				'length' => 1000,
				'null' => false
			])
			->addColumn('content', [
				'type' => 'text',
				'null' => false
			])
			->addColumn('resource', [
				'type' => 'string',
				'length' => 1024,
				'null' => true
			])
			->addColumn('is_important', [
				'type' => 'boolean',
				'null' => false,
				'default' => false
			])
			->addColumn('readed', ['type' => 'datetime', 'null' => true])
			->addColumn('created', ['type' => 'datetime', 'null' => true])
			->addConstraint('pk_notifications', [
				'type' => 'primary',
				'columns' => ['id']
			])
			->addConstraint('fk_notifications_users', [
				'type' => 'foreign',
				'columns' => ['user_id'],
				'references' => ['users', 'id']
			]);

		$this->_installSchema($io, $notificationSchema);
	}

	private function _installRoleSchema(ConsoleIo $io)
	{
		$roleSchema = new TableSchema('roles');
		$roleSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('code', [
				'type' => 'string',
				'length' => 50,
				'null' => false
			])
			->addColumn('name', [
				'type' => 'string',
				'length' => 50,
				'null' => false
			])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addColumn('deleted', ['type' => 'datetime', 'null' => true])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			]);
		$this->_installSchema($io, $roleSchema);
	}

	private function _installRolePermissionSchema(ConsoleIo $io)
	{
		$rolePermissionSchema = new TableSchema('role_permissions');
		$rolePermissionSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'autoIncrement' => true,
				'unsigned' => true,
				'null' => false
			])
			->addColumn('role_id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false
			])
			->addColumn('action', [
				'type' => 'string',
				'length' => 1000,
				'null' => false
			])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			])
			->addConstraint('fk_role_permissions_roles', [
				'type' => 'foreign',
				'columns' => ['role_id'],
				'references' => ['roles', 'id']
			]);
		$this->_installSchema($io, $rolePermissionSchema);
	}

	private function _installUserRoleSchema(ConsoleIo $io)
	{
		$userRoleSchema = new TableSchema('users_roles');
		$userRoleSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('user_id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false
			])
			->addColumn('role_id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false
			])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			])
			->addConstraint('fk_users_roles_users', [
				'type' => 'foreign',
				'columns' => ['user_id'],
				'references' => ['users', 'id']
			])
			->addConstraint('fk_users_roles_roles', [
				'type' => 'foreign',
				'columns' => ['role_id'],
				'references' => ['roles', 'id']
			]);

		$this->_installSchema($io, $userRoleSchema);
	}

	private function _installUserSchema(ConsoleIo $io)
	{
		$userSchema = new TableSchema('users');
		$userSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('username', ['type' => 'string', 'length' => 50, 'null' => false])
			->addColumn('password', ['type' => 'string', 'length' => 100, 'null' => false])
			->addColumn('email', ['type' => 'string', 'length' => 250, 'null' => false])
			->addColumn('status', ['type' => 'string', 'length' => 20, 'null' => false, 'default' => 'active'])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addColumn('deleted', ['type' => 'datetime', 'null' => true])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			]);

		$this->_installSchema($io, $userSchema);
	}

	private function _installVersionSchema(ConsoleIo $io)
	{
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
		$this->_installSchema($io, $versionSchema);
	}

	private function _installSchema(ConsoleIo $io, TableSchema $tableSchema)
	{
		$io->info(sprintf('Installing %s table...', $tableSchema->name()));
		$db = ConnectionManager::get('default');

		$queries = $tableSchema->createSql($db);
		foreach ($queries as $sql) {
			$db->execute($sql);
		}
	}

	private function _dropSchema(string $tableName)
	{
		$db = ConnectionManager::get('default');
		$db->execute(sprintf("DROP TABLE IF EXISTS %s", $tableName));
	}

	private function _insertCommonData()
	{
		$db = ConnectionManager::get('default');
		$roles = [Role::ADMIN, Role::USER, Role::DEVELOPER];
		foreach ($roles as $role) {
			$roleName = Inflector::camelize($role);
			$db->execute("INSERT INTO roles (code, name, created, modified) VALUES('$role', '$roleName', NOW(), NOW())");
		}
		$db->execute("INSERT INTO versions (code, created) VALUES ('1.0.0', NOW())");
	}
}
