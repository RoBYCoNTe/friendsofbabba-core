<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Command\Api\CreateCommand as ApiCreateCommand;
use FriendsOfBabba\Core\Command\Entity\CreateCommand as EntityCreateCommand;

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
		$parser->addArgument('entity', ['required' => true]);
		$parser->addOption('namespace', ['short' => 'n']);
		$parser->addOption('states', ['short' => 's', 'default' => '', 'help' => 'List of states separated by comma', 'default' => 'Draft,Approved']);
		$parser->addOption('transitions', ['short' => 't', 'default' => '', 'help' => 'List of transitions separated by comma: state1:state2', 'default' => 'Draft:Approved']);
		$parser->addOption('erase', ['short' => 'e', 'default' => false, 'help' => 'Erase workflow files before creation (you lost everything!)']);

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
			'Creating entity for transaction...' => 'createTransactionEntity',
			'Creating workflow files...' => 'createFiles',
			'Creating API controller...' => 'createApiController'
		];
		$entity = $args->getArgument('entity');
		foreach ($steps as $message => $step) {
			$io->out(PHP_EOL);
			$io->hr();
			$io->out(sprintf("<info>%s</info> %s", $entity, $message));
			$io->hr();
			$r = $this->{$step}($args, $io);
			if (!is_null($r)) {
				$io->out(sprintf("<error>Error</error> executing %s", $step));
				break;
			}
		}
	}

	/**
	 * Create entity model.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function createEntity(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getArgument('entity');
		$r = $this->executeCommand(EntityCreateCommand::class, [$entity, "--theme", "FriendsOfBabba/Core"], $io);

		return $r;
	}

	/**
	 * Create entity transaction table.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function createTransactionTable(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getArgument('entity');
		$namespace = $args->getOption('namespace');

		$r = $this->executeCommand(CreateTransactionTableCommand::class, [$entity, '--namespace', $namespace], $io);

		return $r;
	}

	/**
	 * Create entity for transaction.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function createTransactionEntity(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getArgument('entity');
		$entity = Inflector::singularize($entity);

		$r = $this->executeCommand(EntityCreateCommand::class, [$entity . "Transactions"], $io);

		return $r;
	}

	/**
	 * Create workflow files.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function createFiles(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getArgument('entity');
		$namespace = $args->getOption('namespace');
		$states = $args->getOption('states');
		$transitions = $args->getOption('transitions');

		$erase = $args->getOption('erase');
		$args = [
			$entity,
			'--states', $states,
			'--transitions', $transitions
		];
		if ($erase) {
			$args[] = '--erase';
			$args[] = $erase;
		}
		if ($namespace) {
			$args[] = '--namespace';
			$args[] = $namespace;
		}

		$r = $this->executeCommand(CreateFilesCommand::class, $args, $io);

		return $r;
	}

	/**
	 * Create API controller.
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function createApiController(Arguments $args, ConsoleIo $io): ?int
	{
		$entity = $args->getArgument('entity');
		$r = $this->executeCommand(ApiCreateCommand::class, [$entity], $io);

		return $r;
	}
}
