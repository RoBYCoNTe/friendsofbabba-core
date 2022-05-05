<?php

namespace FriendsOfBabba\Core\Controller\Api;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use FriendsOfBabba\Core\Model\Entity\Language;
use FriendsOfBabba\Core\Model\Entity\LanguageMessage;
use FriendsOfBabba\Core\Model\Table\LanguageMessagesTable;
use FriendsOfBabba\Core\Model\Table\LanguagesTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * @property LanguagesTable $Languages
 * @property LanguageMessagesTable $LanguageMessages
 */
class LanguagesController extends AppController
{
	public function initialize(): void
	{
		parent::initialize();
		$this->loadModel(PluginManager::getInstance()->getFQN("Languages"));
		$this->loadModel(PluginManager::getInstance()->getFQN("LanguageMessages"));
		$this->Authentication->addUnauthenticatedActions(['load']);
		$debug = Configure::read("debug");
		if ($debug === true) {
			$this->Authentication->addUnauthenticatedActions(["putMessage"]);
		}
	}

	public function load()
	{
		if (($languages = Cache::read("languages")) === null) {
			/**
			 * @var Language[]
			 */
			$languages = $this->Languages->find()
				->contain(["LanguageMessages"])
				->toList();
			$dictionary = [];
			foreach ($languages as $language) {
				$dictionary[$language->code] = [];
				foreach ($language->messages as $message) {
					$dictionary[$language->code][$message->code] = $message->text;
				}
			}

			$languages = $dictionary;
			if (Configure::read("debug") === false) {
				Cache::write("languages", $languages);
			}
		}
		$this->set([
			'success' => true,
			'data' => $languages,
			'_serialize' => ['success', 'data']
		]);
	}

	public function putMessage()
	{
		if (!$this->request->is("PUT")) {
			throw new NotFoundException();
		}

		$language = $this->Languages->findOrCreate([
			'code' => $this->request->getData("code")
		], function (Language $language) {
			$language->name = $language->code;
		});
		$message = $this->LanguageMessages->findOrCreate([
			'language_id' => $language->id,
			'code' => $this->request->getData("message.code")
		], function (LanguageMessage $languageMessage) {
			$languageMessage->text = $this->request->getData("message.text");
		});

		Cache::delete("languages");

		$this->set([
			'data' => $message,
			'success' => !$language->hasErrors(),
			'_serialize' => ['data', 'success']
		]);
	}
}
