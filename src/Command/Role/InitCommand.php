<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Role;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Role;
use FriendsOfBabba\Core\Model\Table\RolesTable;
use FriendsOfBabba\Core\Notification\NotificationTrait;

/**
 * Initialize basic roles.
 * This command is executed automatically with installation migrations
 * but you can use it manually to initialize basic roles.
 *
 * @property RolesTable $Roles
 */
class InitCommand extends Command
{
	use NotificationTrait;

	public function initialize(): void
	{
		parent::initialize();

		$this->Roles = $this->fetchTable('FriendsOfBabba/Core.Roles');
	}
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
	 * Implement this method with your command's logic.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$roles = [Role::ADMIN, Role::USER, Role::DEVELOPER];
		$saved = 0;
		foreach ($roles as $role) {
			$roleName = Inflector::camelize($role);
			$roleEntity = $this->Roles->findByCode($role)->first();
			if (!$roleEntity) {
				$roleEntity = $this->Roles->newEntity([
					'code' => $role,
					'name' => $roleName,
				]);
				$this->Roles->save($roleEntity);
				$saved++;
			}
		}
		$io->out('<success>' . $saved . ' roles saved</success>');
	}
}
