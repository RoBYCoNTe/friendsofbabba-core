<?php

namespace FriendsOfBabba\Core\Model\Behavior;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;
use Cake\Utility\Text;

class TextBehavior extends Behavior
{
	protected $_defaultConfig = [
		'ucwords' => [],
		'uppercases' => [],
		'lowercases' => [],
		'numbers' => [],
		'mobile-phones' => [],
		'slugs' => [],
		'slugs-underscore' => []
	];

	private function _process(ArrayObject $data): void
	{
		$config = $this->getConfig();
		if (count($config['ucwords'])) {
			foreach ($config['ucwords'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] =  ucwords(strtolower(Hash::get($data, $field)));
				}
			}
		}
		if (count($config['uppercases'])) {
			foreach ($config['uppercases'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] =  strtoupper(Hash::get($data, $field));
				}
			}
		}
		if (count($config['lowercases'])) {
			foreach ($config['lowercases'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] =  strtolower(Hash::get($data, $field));
				}
			}
		}
		if (count($config['numbers'])) {
			foreach ($config['numbers'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] = preg_replace('/\D/', '', Hash::get($data, $field));
				}
			}
		}
		if (count($config['mobile-phones'])) {
			foreach ($config['mobile_phones'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] = preg_replace('/[^0-9\+,]/', '', Hash::get($data, $field));
				}
			}
		}
		if (count($config['slugs'])) {
			foreach ($config['slugs'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] = strtolower(Text::slug(Hash::get($data, $field)));
				}
			}
		}
		if (count($config['slugs-underscore'])) {
			foreach ($config['slugs-underscore'] as $field) {
				if (Hash::get($data, $field) !== null) {
					$data[$field] = strtolower(Text::slug(Hash::get($data, $field), '_'));
				}
			}
		}
	}

	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
	{
		$this->_process($data);
	}
}
