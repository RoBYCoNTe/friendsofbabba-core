<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query;
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
		$this->Crud->on('afterSave', function () {
			// Every time a language message is saved, we need
			// to generate the language CSV files.
			$this->_export();
		});
	}

	public function index()
	{
		$this->Crud->on("beforePaginate", function (Event $event) {
			/** @var Query */
			$query = $event->getSubject()->query;
			$query = $query->contain(["Languages"]);
			$query = $this->Authorization->applyScope($query);
		});
		$this->Crud->execute();
	}

	public function add()
	{
		$resource = $this->request->getData('resource');

		if ($resource) {
			return $this->_generate($resource, $this->request->getQuery('language_id', null));
		}
		$this->Crud->on('beforeSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
			unset($entity->language);
		});
		return $this->Crud->execute();
	}

	public function edit()
	{
		$this->Crud->on('beforeSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
			unset($entity->language);
		});
		$this->Crud->on('afterSave', function (Event $event) {
			$entity = $event->getSubject()->entity;
			$entity->language = (new TableLocator())
				->get('Languages')
				->findById($entity->language_id)
				->first();

			$this->set('data', $entity);
			$this->Crud->action()->setConfig('serialize.data', 'data');
		});
		return $this->Crud->execute();
	}

	public function delete()
	{
		$this->Crud->on('afterFind', function (Event $event) {
			$entity = $event->getSubject()->entity;
			if (!$this->Authorization->can($entity)) {
				throw new ForbiddenException();
			}
		});
		$this->Crud->on('afterDelete', function (Event $event) {
			$this->_export();
		});
		return $this->Crud->execute();
	}

	/**
	 * Generate localized strings for specified resource and language.
	 *
	 * @param string $resource
	 * 		The resource to generate localized strings for.
	 * @param integer|null $selectedLanguage
	 * 		The language to generate localized strings for.
	 * @return void
	 */
	private function _generate(string $resource, ?int $selectedLanguage = null): void
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
			$data[$fieldsKey][$column . ".help"] = "$key.fields.$column.help";
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
					if (!$this->Authorization->can($languageMessage, 'add')) {
						continue;
					}
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

	private function _export()
	{
		$command = new LanguageCommand;
		return $command->executeCommand($command, ['export']);
	}
}
