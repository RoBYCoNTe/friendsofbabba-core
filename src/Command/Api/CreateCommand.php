<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Api;

use Bake\Utility\TemplateRenderer;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\PluginManager;

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

		$renderer = new TemplateRenderer('');
		$renderer->set(['name' => Inflector::pluralize($entity)]);
		$out = $renderer->generate(PluginManager::getInstance()->getFQN('Controller/Api/controller'));
		$filename = sprintf('%s/Controller/Api/%sController.php', APP, Inflector::pluralize($entity));

		$io->createFile($filename, $out);
	}
}
