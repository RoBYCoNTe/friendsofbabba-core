<?php

namespace App\Command\Migration;

use Cake\ORM\Locator\TableLocator;

class _ExampleMigration extends AbstractMigration
{
	public function getMappers()
	{
		return [
			'id' => 'id_example',
			'name' => 'name'
		];
	}

	public function sync($limit, $offset)
	{
		$Table = (new TableLocator)->get('_Example');

		$sql = "SELECT * FROM";
		$results = $this->connection
			->execute($sql)
			->fetchAll('assoc');

		$this->Io->info(count($results) . " _example found");
		$this->Io->hr();

		$this->getEntities($Table, $results, [], function ($entity) use ($Table) {
			$this->Io->out("|__ Processing {$entity->id}");
			$result = $Table->save($entity);
			if ($result) {
				$this->Io->success("|__ {$entity->id} saved :-D");
			} else {
				$this->Io->warning("|__ {$entity->id}, not saved: " . json_encode($entity->getErrors()));
			}
		});
	}
}
