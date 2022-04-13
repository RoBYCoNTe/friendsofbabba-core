<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Role;

class DataInstaller extends Installer
{
	public function install(ConsoleIo $io): void
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
