<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\User;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use FriendsOfBabba\Core\Model\Table\UsersTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * Users/Pwd command.
 *
 * @property UsersTable $Users
 */
class PwdCommand extends Command
{
	public function initialize(): void
	{
		parent::initialize();
		$this->loadModel(PluginManager::instance()->getModelFQN('Users'));
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
		$parser->addArgument('username', ['required' => true]);
		$parser->addArgument('password', ['required' => true]);

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
		$username = $args->getArgument('username');
		$password = $args->getArgument('password');

		$user = $this->Users->findByUsername($username)->first();
		if (!$user) {
			$io->error('User not found');
			return 1;
		}

		$user->password = $password;
		$this->Users->save($user);

		$io->success(sprintf('User password changed: %s', $password));
	}
}
