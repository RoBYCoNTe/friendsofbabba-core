<?php

namespace FriendsOfBabba\Core\Command\DataMigration;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

class ExecuteCommand extends Command
{
	public $io;

	public function initialize(): void
	{
		Configure::write('softDelete', false);
		parent::initialize();
	}

	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser->addArgument('command', ['help' => 'The type data migration to execute.', 'required' => true]);
		$parser->addOption('limit', [
			'help' => 'The limit of old records you want to migrate',
			'required' => false,
			'default' => 4000
		]);
		$parser->addOption('offset', [
			'help' => 'The offset of old records you want to migrate',
			'required' => false,
			'default' => 0
		]);
		$parser->addOption('namespace', [
			'help' => 'Namespace where data migrations are stored.',
			'required' => false,
			'default' => "\\App\\Command\\DataMigration"
		]);
		return $parser;
	}

	public function execute(Arguments $args, ConsoleIo $io)
	{
		$command = $args->getArgument('command');
		$name = Inflector::camelize($command);

		$limit = $args->getOption('limit');
		$offset = $args->getOption('offset');
		$namespace = $args->getOption('namespace');

		$className = "{$namespace}\\{$name}DataMigration";

		if (!class_exists($className)) {
			$io->error(__d(
				"friendsofbabba_core",
				"No class found, please create {$className}"
			));
			$io->info(__d(
				"friendsofbabba_core",
				"To create data migration execute: bin/cake data-migration create {$name}"
			));
			return;
		}

		/** @var AbstractDataMigration $dataMigration */
		$dataMigration = new $className();
		$dataMigration->sync($io, $limit, $offset);
	}
}
