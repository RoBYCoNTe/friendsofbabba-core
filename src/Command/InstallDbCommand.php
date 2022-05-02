<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

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
		$parser->addOption('filter', [
			'help' => 'Filter the entities to create',
			'short' => 'f',
			'default' => '',
		]);

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
		if ($io->askChoice('Do you want to (re)install database?', ['yes', 'no'], 'no') === 'no') {
			return;
		}

		// Execution order is important, please do not change it without good reason.
		$packages = [
			'versions' => \FriendsOfBabba\Core\Command\Install\Db\VersionInstaller::class,
			'commands' => \FriendsOfBabba\Core\Command\Install\Db\CommandInstaller::class,
			'language' => \FriendsOfBabba\Core\Command\Install\Db\LanguageInstaller::class,
			'user' => \FriendsOfBabba\Core\Command\Install\Db\UserInstaller::class,
			'media' => \FriendsOfBabba\Core\Command\Install\Db\MediaInstaller::class,
			'data' => \FriendsOfBabba\Core\Command\Install\Db\DataInstaller::class,
			'ticket' => \FriendsOfBabba\Core\Command\Install\Db\TicketInstaller::class
		];

		$io->info('Installing basic tables...');
		$filter = $args->getOption('filter');
		if ($filter) {
			$filter = explode(',', $filter);
			$packages = array_intersect_key($packages, array_flip($filter));
		}

		foreach ($packages as $package => $installerClass) {
			$io->info('Installing ' . $package . '...');
			$installer = new $installerClass();
			$installer->install($io);
		}


		$io->overwrite('<success>Database installation completed!</success>', 1);
	}
}
