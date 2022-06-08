<?php

namespace FriendsOfBabba\Core\Command;

use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Provide basic logging functionality for cakephp commands.
 * You can declare and use new instances of this class everywhere in your code.
 *
 * Every logged operation will be written in to the database.
 *
 * @example
 * // Use logger:
 * $logger = new CommandLog(['name' => 'bin/cake command']);
 * $logger->info('Something');
 *
 *
 */
class CommandLog
{
	public $repository;
	public $entity;
	public $output;
	public $Io;

	public function __construct($config = [])
	{
		$commandLogs = TableRegistry::getTableLocator()->get('FriendsOfBabba/Core.CommandLogs');
		$this->entity = $commandLogs->newEntity([
			'id' => Hash::get($config, 'id'),
			'command' => Hash::get($config, 'name', uniqid())
		]);

		$commandLogs->save($this->entity);

		$this->Io = new ConsoleIo();
		$this->output = Hash::get($config, 'output', true);
	}

	private function write(string $type, string $output, bool $debug = true)
	{
		$commandLogRows = TableRegistry::getTableLocator()->get('FriendsOfBabba/Core.CommandLogRows');
		$row = $commandLogRows->newEntity([
			'command_log_id' => $this->entity->id,
			'type' => $type,
			'output' => $output
		]);

		if ($this->output && $debug === true) {
			$this->Io->{$type}($output);
		}

		return $commandLogRows->save($row);
	}

	public function out(string $output, bool $debug = true)
	{
		return $this->write('out', $output, $debug);
	}

	public function info(string $output, bool $debug = true)
	{
		return $this->write('info', $output, $debug);
	}

	public function warning(string $output, bool $debug = true)
	{
		return $this->write('warning', $output, $debug);
	}

	public function error(string $output, bool $debug = true)
	{
		return $this->write('error', $output, $debug);
	}

	public function success(string $output, bool $debug = true)
	{
		return $this->write('success', $output, $debug);
	}

	public function hr()
	{
		$this->Io->hr();
		return $this->write('out', '-------------------------------------------------------------------------------', false);
	}

	public function nl()
	{
		$this->Io->nl();
		return $this->write('out', "\n", false);
	}
}
