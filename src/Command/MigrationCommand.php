<?php

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Hook\HookManager;

class MigrationCommand extends Command
{
	public $io;

	public function initialize(): void
	{
		Configure::write('softDelete', false);
		parent::initialize();
	}

	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser->addArgument('command', [
			'help' => 'The type of command you need to execute.',
			'required' => true,
			'choices' => ['sync', 'init'],
			'default' => 'sync'
		]);
		$parser->addArgument('table', [
			'help' => 'The new table you want to migrate',
			'required' => false,
			'default' => 'nothing'
		]);
		$parser->addArgument('limit', [
			'help' => 'The limit of old records you want to migrate',
			'required' => false,
			'default' => 4000
		]);
		$parser->addArgument('offset', [
			'help' => 'The offset of old records you want to migrate',
			'required' => false,
			'default' => 0
		]);
		$parser->addArgument('namespace', [
			'help' => 'Namespace where migrations are stored.',
			'required' => false,
			'default' => "App\\Command\\Migration"
		]);
		$parser->addArgument('statement', [
			'help' => 'Custom sql statement',
			'required' => false,
			'default' => ""
		]);
		return $parser;
	}

	public function execute(Arguments $args, ConsoleIo $io)
	{
		$this->Io = $io;
		$command = $args->getArgument('command');

		if ($command === 'init') {
			return $this->init();
		}

		$table = $args->getArgument('table');
		$limit = $args->getArgument('limit');
		$offset = $args->getArgument('offset');
		$statement = $args->getArgument('statement');
		$namespace = $args->getArgument('namespace', "App\\Command\\Migration");
		$name = Inflector::camelize($table);

		$className = "{$namespace}\\{$name}Migration";

		if (!class_exists($className)) {
			$io->error("No class found, please create {$className}");
			return;
		}

		$migration = new $className($io);
		$migration->sync($limit ? $limit : 4000, $offset ? $offset : 0, $statement ? $statement : "");
	}

	private function init()
	{
		$this->Io->hr();
		$this->Io->info("Preparing DB before syncing users");
		$this->Io->hr();

		$connection = ConnectionManager::get('default');

		HookManager::instance()->fire('Command/MigrationCommand.init', [$connection]);

		$this->Io->hr();
	}
}
