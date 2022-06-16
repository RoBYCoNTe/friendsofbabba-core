<?php

namespace FriendsOfBabba\Core\Command\DataMigration;

use Bake\Utility\TemplateRenderer;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;

class CreateCommand extends Command
{
	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser->setDescription('Create a new data migration PHP script.');
		$parser->addArgument('name', [
			'help' => 'The name of the migration',
			'short' => 'n',
			'required' => true
		]);
		$parser->addOption('theme', [
			'help' => 'The theme to use for the migration',
			'required' => true,
			'default' => 'FriendsOfBabba/Core'
		]);
		$parser->addOption('connection', [
			'help' => 'The connection to use for the migration',
			'required' => true,
			'default' => 'default'
		]);
		$parser->addOption('remote', [
			'help' => 'Name of the remote table to migrate',
			'required' => true,
			'default' => 'remote_table'
		]);
		$parser->addOption('local', [
			'help' => 'Name of the local table to migrate',
			'required' => true,
			'default' => 'local_table'
		]);
		return $parser;
	}

	public function execute(Arguments $args, ConsoleIo $io)
	{
		$name = $args->getArgument('name');
		$theme = $args->getOption('theme');

		$remote = $args->getOption('remote');
		$remote = Inflector::underscore($remote);

		$local = $args->getOption('local');
		$local = Inflector::camelize($local);

		$connection = $args->getOption('connection');

		$io->info(sprintf("\nBaking data migration class %s...\n", $name));

		$renderer = new TemplateRenderer($theme);
		$renderer->set(compact('name', 'local', 'remote', 'connection'));
		$out = $renderer->generate('Console/DataMigration/abstract');
		$filepath = sprintf("%sCommand/DataMigration/%sDataMigration.php", APP, $name);
		$io->createFile($filepath, $out);
	}
}
