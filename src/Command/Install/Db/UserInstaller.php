<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class UserInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$this->dropSchema("notifications");
		$this->dropSchema("users_roles");
		$this->dropSchema("user_profiles");
		$this->dropSchema("users");

		$this->dropSchema("role_permissions");
		$this->dropSchema("roles");

		$this->_installRoleSchema($io);
		$this->_installRolePermissionSchema($io);

		$this->_installUserSchema($io);
		$this->_installUserProfileSchema($io);
		$this->_installUserRoleSchema($io);
		$this->_installNotificationSchema($io);
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
		$this->installSchema($io, $roleSchema);
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
		$this->installSchema($io, $rolePermissionSchema);
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

		$this->installSchema($io, $userRoleSchema);
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

		$this->installSchema($io, $userSchema);
	}

	private function _installUserProfileSchema(ConsoleIo $io)
	{
		$userSchema = new TableSchema('user_profiles');
		$userSchema
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
			->addColumn('name', ['type' => 'string', 'length' => 50, 'null' => false])
			->addColumn('surname', ['type' => 'string', 'length' => 100, 'null' => false])
			->addColumn('created', ['type' => 'datetime', 'null' => false])
			->addColumn('modified', ['type' => 'datetime', 'null' => false])
			->addColumn('deleted', ['type' => 'datetime', 'null' => true])
			->addConstraint('fk_user_profiles_users', [
				'type' => 'foreign',
				'columns' => ['user_id'],
				'references' => ['users', 'id']
			])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			]);

		$this->installSchema($io, $userSchema);
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

		$this->installSchema($io, $notificationSchema);
	}
}
