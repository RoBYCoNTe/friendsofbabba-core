<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/**
 * Create Workflow.
 *
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
		$parser->addOption('entity', ['short' => 'e']);
		$parser->addOption('plugin', ['short' => 'p', 'default' => '', 'help' => 'Plugin name']);
		$parser->addOption('namespace', ['short' => 'n']);
		$parser->addOption('states', ['short' => 's', 'default' => '', 'help' => 'List of states separated by comma', 'default' => 'Draft,Approved']);
		$parser->addOption('routes', ['short' => 'r', 'default' => '', 'help' => 'List of routes separated by comma: state1:state2', 'default' => 'Draft:Approved']);

		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$steps = [
			'Creating entity model...' => 'createEntity',
			'Creating entity transaction table...' => 'createTransactionTable',
			'Creating workflow files...' => 'createFiles'
		];
		foreach ($steps as $message => $step) {
			$io->out($message);
			$r = $this->{$step}($args, $io);
			if (!is_null($r)) {
				$io->error(sprintf('Unable to complete the task, exit code: %s', $r));
				break;
			}
		}
	}

	public function createEntity(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getOption('entity');
		$plugin = $args->getOption('plugin');

		$r = $this->executeCommand(CreateEntityCommand::class, [
			'--entity', $entity,
			'--plugin', $plugin,
		], $io);

		return $r;
	}

	public function createTransactionTable(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getOption('entity');
		$namespace = $args->getOption('namespace');

		$r = $this->executeCommand(CreateTransactionTableCommand::class, [
			'--entity', $entity,
			'--namespace', $namespace,
		], $io);

		return $r;
	}

	public function createFiles(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getOption('entity');
		$namespace = $args->getOption('namespace');
		$states = $args->getOption('states');
		$routes = $args->getOption('routes');

		$args = [
			'--entity', $entity,
			'--states', $states,
			'--routes', $routes
		];
		if ($namespace) {
			$args[] = '--namespace';
			$args[] = $namespace;
		}

		$r = $this->executeCommand(CreateFilesCommand::class, $args, $io);

		return $r;
	}
}
