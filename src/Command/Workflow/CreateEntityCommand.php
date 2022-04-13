<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Bake\Command\ModelCommand;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;

/**
 * Create Entity
 */
class CreateEntityCommand extends Command
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
		$parser->addOption('entity', ['required' => true]);
		$parser->addOption('plugin');

		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$entity = $args->getOption('entity');
		$plugin = $args->getOption('plugin');
		$table = Inflector::tableize($entity);
		$args = [
			$table,
			"--no-test",
			"--no-fixture",
		];
		if (!empty($plugin)) {
			$args[] = "--plugin";
			$args[] = $plugin;
		}

		$this->executeCommand(ModelCommand::class, $args, $io);

		$this->_adjustModel($entity, $plugin, $io);
	}

	private function _adjustModel(string $entity, string $plugin, ConsoleIo $io): void
	{
		$entity = Inflector::singularize($entity);
		$entity = Inflector::camelize($entity);
		if ($plugin) {
			$path = ROOT . DS . 'plugins' . DS . $plugin . DS . 'src' . DS . 'Model' . DS . 'Entity' . DS . $entity . '.php';
		} else {
			$path = APP . 'Model' . DS . 'Entity' . DS . $entity . '.php';
		}
		$io->info(sprintf("Path: %s", $path));
		if (!file_exists($path)) {
			$io->warning(sprintf("File not found: %s", $path));
		}
		$content = file_get_contents($path);
		$replacement = implode("", [
			"protected \$_accessible = [",
			"\n\t\t// Virtual Fields (Workflow)",
			"\n\t\t'state' => true,",
			"\n\t\t'notes' => true,",
			"\n\t\t'is_private' => true,",
			"\n\t\t'transaction' => true,",
			"\n\t\t",
			"\n\t\t// Table Fields"
		]);
		if (strpos($content, $replacement) === false) {
			$content = str_replace(
				"protected \$_accessible = [",
				$replacement,
				$content
			);

			file_put_contents($path, $content);
			$io->success(sprintf("Adjusted %s", $path));
		}
	}
}
