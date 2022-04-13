<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Api;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;

/**
 * Create API controller command.
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
		$parser->addArgument('entity', [
			'help' => 'The entity for which generate the API controller.',
			'required' => true
		]);

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
		$entity = $args->getArgument('entity');

		$io->out(sprintf("\nBaking API controller for %s...\n", $entity));

		$templatePaths = [
			ROOT . DS . "plugins/FriendsOfBabba/Core/src/Controller/Api/ApiController.template.php",
			ROOT . DS . "vendor/friendsofbabba/core/src/Controller/Api/ApiController.template.php",
		];
		$template = "";
		foreach ($templatePaths as $templatePath) {
			if (file_exists($templatePath)) {
				$io->out(sprintf("<success>Found</success> Template file `%s` ", $templatePath));
				$template = file_get_contents($templatePath);
				break;
			}
			$io->warning(sprintf("Template file `%s` not exists.", $templatePath));
		}

		if (empty($template)) {
			$io->warning("No template file found. Filter collection will not be created.");
			return;
		}
		$singular = Inflector::singularize($entity);
		$path = ROOT . DS . "src/Controller/Api/{$singular}Controller.php";

		if (file_exists($path)) {
			$io->warning(sprintf("File `%s` exists.", $path));
			if ($io->askChoice('Do you want to overwrite?', ['y', 'n'], 'n') === 'n') {
				return;
			}
		}

		$template = str_replace("Entities", Inflector::pluralize($entity), $template);
		$template = str_replace("Entity", Inflector::singularize($entity), $template);

		file_put_contents($path, $template);

		$io->out(sprintf("<success>Wrote</success> `%s`", $path));
	}
}
