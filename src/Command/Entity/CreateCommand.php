<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Entity;

use Bake\Command\ModelCommand;
use Cake\Command\CacheClearallCommand;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;

/**
 * Create Entity
 */
class CreateCommand extends Command
{
	public function initialize(): void
	{
		parent::initialize();
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
		$parser->addArgument('entity', ['required' => true, 'help' => 'The entity for which generate the model and tables.']);
		$parser->addOption('theme', ['help' => 'The theme to use when baking code.', 'short' => 't', 'default' => 'FriendsOfBabba/Core']);
		$parser->addOption('connection', ['help' => 'The connection to use.', 'short' => 'c', 'default' => 'default']);
		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$entity = $args->getArgument('entity');
		$table = Inflector::tableize($entity);
		$theme = $args->getOption('theme');
		$argv = [
			$table,
			"--no-test",
			"--no-fixture"
		];

		if ($theme) {
			$argv[] = "--theme";
			$argv[] = $theme;
		}
		$connection = $args->getOption('connection');
		if ($connection) {
			$argv[] = "--connection";
			$argv[] = $connection;
		}

		$this->executeCommand(CreateFilterCollectionCommand::class, [$entity]);
		$this->executeCommand(ModelCommand::class, $argv, $io);
		$this->executeCommand(CacheClearallCommand::class);
	}
}
