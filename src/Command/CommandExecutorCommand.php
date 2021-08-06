<?php

namespace FriendsOfBabba\Core\Command;

use Cake\Command\Command as CakeCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Utility\Hash;
use FriendsOfBabba\Core\Model\Entity\Command;

class CommandExecutorCommand extends CakeCommand
{
	public $io;

	public function initialize(): void
	{
		parent::initialize();
		$this->loadModel('FriendsOfBabba/Core.Commands');
		$this->loadModel('FriendsOfBabba/Core.Notifications');
	}

	private function prefetch($commands)
	{
		foreach ($commands as $command) {
			$command->status = Command::STATUS_EXECUTING;
			$this->Commands->save($command);
		}
	}

	public function execute(Arguments $args, ConsoleIo $io)
	{
		$commands = $this->Commands
			->find()
			->where([
				'Commands.execute_at <=' => new \DateTime(),
				'Commands.status' => Command::STATUS_PENDING
			])
			->all();

		if ($commands->count() <= 0) {
			return $io->warning("Nothing to execute");
		}

		$this->prefetch($commands);

		foreach ($commands as $command) {
			$cakeCommand = new $command->fullname();
			$result = $cakeCommand->executeCommand($cakeCommand, $command->args);
			$success = $result === NULL;
			$command->result = [
				'success' => $result === NULL,
				'code' => $result
			];
			$command->status = $success
				? Command::STATUS_EXECUTED
				: Command::STATUS_ERROR;
			$this->Commands->save($command);

			// Notify associated user
			if ($command->user_id && is_array($command->notify_args) && $success) {
				$notify = $this->Notifications->newEntity(Hash::merge($command->notify_args, [
					'user_id' => $command->user_id
				]));
				$this->Notifications->save($notify);
			}
		}
	}
}
