<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Entity;

use Bake\Utility\TemplateRenderer;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;

/**
 * Create Filter Collection command
 */
class CreateFilterCollectionCommand extends Command
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
		$io->out(sprintf("\nBaking filter collection class for %s...\n", $entity));
		$renderer = new TemplateRenderer();
		$renderer->set([
			'nameSingular' => Inflector::singularize($entity),
			'name' => Inflector::pluralize($entity)
		]);
		$out = $renderer->generate('FriendsOfBabba/Core.Model/filter');
		$filename = sprintf('%s/Model/Filter/%sCollection.php', APP, Inflector::singularize($entity));
		$io->createFile($filename, $out);
	}
}
