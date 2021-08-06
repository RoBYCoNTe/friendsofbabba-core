<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\ORM\Locator\TableLocator;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Command\LanguageCommand;

class LanguageMessagesController extends AppController
{
	public $paginate = [
		'page' => 1,
		'limit' => 5,
		'maxLimit' => 200
	];

	public function initialize(): void
	{
		parent::initialize();

		$this->Crud->useModel('FriendsOfBabba/Core.LanguageMessages');
	}

	public function index()
	{
		$this->Crud->on("beforePaginate", function (Event $event) {
			$query = $event->getSubject()->query;
			$event->getSubject()->query = $query->contain([
				"Languages"
			]);
		});
		$this->Crud->execute();
	}

	public function add()
	{
		$resource = $this->request->getData('resource');
		if ($resource) {
			return $this->generate($resource, $this->request->getQuery('language_id', null));
		}
		$this->Crud->on('beforeSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			unset($entity->language);
		});
		$this->Crud->on('afterSave', function (Event $event) {
			$this->export();
		});
		return $this->Crud->execute();
	}

	public function edit()
	{
		$this->Crud->on('afterSave', function (Event $event) {
			$this->export();
			$entity = $event->getSubject()->entity;
			$this->set('data', $entity);
			$entity->language = (new TableLocator())
				->get('Languages')
				->findById($entity->language_id)
				->first();
			$this->Crud->action()->setConfig('serialize.data', 'data');
		});
		return $this->Crud->execute();
	}

	public function delete()
	{
		$this->Crud->on('afterDelete', function (Event $event) {
			$this->export();
		});
		return $this->Crud->execute();
	}

	public function generate($resource, $selectedLanguage = null)
	{
		$humanResource = Inflector::humanize($resource, '-');
		$repository = (new TableLocator())->get(Inflector::camelize($resource, '-'));
		$columns = $repository->getSchema()->columns();
		$key = "resources.{$resource}";
		$fieldsKey = "$key.fields";
		$data = [
			"$key.name" => Inflector::singularize($humanResource) . ' |||| ' . $humanResource,
			$fieldsKey => []
		];

		foreach ($columns as $column) {
			$data[$fieldsKey][$column] = Inflector::humanize($column);
		}

		$data = Hash::flatten($data);
		$languages = (new TableLocator())
			->get('Languages')
			->find();

		if ($selectedLanguage) {
			$languages->where([
				'Languages.id' => $selectedLanguage
			]);
		}

		foreach ($data as $code => $text) {
			foreach ($languages as $language) {
				$exists = $this->LanguageMessages
					->find()
					->where([
						'LanguageMessages.language_id' => $language->id,
						'LanguageMessages.code' => $code
					])
					->count();
				if (!$exists) {
					$languageMessage = $this->LanguageMessages->newEntity([
						'language_id' => $language->id,
						'code' => $code,
						'text' => $text,
					]);
					$this->LanguageMessages->save($languageMessage);
				}
			}
		}

		$this->export();

		$this->set([
			'data' => $data,
			'success' => true,
			'_serialize' => ['data', 'success']
		]);
	}

	private function export()
	{
		$command = new LanguageCommand;
		return $command->executeCommand($command, ['export']);
	}
}
