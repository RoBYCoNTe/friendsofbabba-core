<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Entity;

use Bake\Command\ModelCommand;
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
		$args = [
			$table,
			"--no-test",
			"--no-fixture",
		];

		$this->executeCommand(ModelCommand::class, $args, $io);

		$this->_adjustAccessibleFields($entity, $io);
		$this->_createFilterCollection($entity, $io);
	}

	private function _createFilterCollection(string $entity, ConsoleIo $io): void
	{
		$io->out(sprintf("\nBaking filter collection class for %s...\n", $entity));

		$templatePaths = [
			ROOT . DS . "plugins/FriendsOfBabba/Core/src/Model/Filter/FilterCollection.template.php",
			ROOT . DS . "vendor/friendsofbabba/core/src/Model/Filter/FilterCollection.template.php",
		];

		$template = "";
		foreach ($templatePaths as $templatePath) {
			if (file_exists($templatePath)) {
				$template = file_get_contents($templatePath);
				$io->out(sprintf("<success>Found</success> Template file `%s` ", $templatePath));
				break;
			}
			$io->warning(sprintf("Template file `%s` not exists.", $templatePath));
		}
		if (empty($template)) {
			$io->warning("No template file found. Filter collection will not be created.");
			return;
		}

		$template = str_replace("Entities", Inflector::pluralize($entity), $template);
		$template = str_replace("Entity", Inflector::singularize($entity), $template);

		$path = APP . 'Model' . DS . 'Filter' . DS . Inflector::singularize($entity) . 'Collection.php';
		if (file_exists($path)) {
			$io->warning(sprintf("File `%s` exists.", $path));
			if ($io->askChoice('Do you want to overwrite?', ['y', 'n'], 'n') === 'n') {
				return;
			}
		}

		$path = APP . 'Model' . DS . 'Table' . DS . Inflector::pluralize($entity) . 'Table.php';
		$content = file_get_contents($path);
		$singular = Inflector::singularize($entity);
		$replacement = implode("", [
			"\n\n\t\t\$this->addBehavior('Search.Search',[",
			"\n\t\t\t'collectionClass' => \App\Model\Filter\\{$singular}Collection::class",
			"\n\t\t]);"
		]);
		if (strpos($content, $replacement) === false) {
			$content = str_replace("parent::initialize(\$config);", implode("", [
				"parent::initialize(\$config);",
				$replacement
			]), $content);
			file_put_contents($path, $content);
			$io->out(sprintf("<success>Wrote</success> `%s`", $path));
		} else {
			$io->out(sprintf("<info>Skipped</info> `%s`", $path));
		}
	}

	private function _adjustAccessibleFields(string $entity, ConsoleIo $io): void
	{
		$io->out(sprintf("\nBaking accessible fields for %s...\n", $entity));

		$path = APP . 'Model' . DS . 'Entity' . DS . Inflector::singularize($entity) . '.php';
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
			$io->out(sprintf("<success>Wrote</success> `%s`", $path));
		} else {
			$io->out(sprintf("<info>Skipped</info> `%s`", $path));
		}
	}
}
