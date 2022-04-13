<?php

namespace FriendsOfBabba\Core\Command\Install\Db;

use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;

class TicketInstaller extends Installer
{
	public function install(ConsoleIo $io): void
	{
		$ticketSchema = new TableSchema('tickets');
		$ticketSchema
			->addColumn('id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false,
				'autoIncrement' => true
			])
			->addColumn('user_id', [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => true,
				'null' => false
			])
			->addColumn('subject', [
				'type' => 'string',
				'length' => 1000,
				'null' => false,
				'default' => '1.0.0'
			])
			->addColumn('created', [
				'type' => 'datetime',
				'null' => false
			])
			->addColumn('modified', [
				'type' => 'datetime',
				'null' => false
			])
			->addConstraint('primary', [
				'type' => 'primary',
				'columns' => ['id']
			])
			->addConstraint('fk_tickets_users', [
				'type' => 'foreign',
				'columns' => ['user_id'],
				'references' => ['users', 'id']
			]);

		$this->installSchema($io, $ticketSchema);
	}
}
