<?php

namespace FriendsOfBabba\Core\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\Model\Entity\Media;


class MediaBehavior extends Behavior
{
	protected $_defaultConfig = [];

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$fields = $this->getConfig();

		foreach ($fields as $field => $config) {
			$field = is_string($config) ? $config : $field;
			$field = Inflector::underscore($field);
			$media = Hash::get($data, $field);

			if (!isset($media['data']) && !isset($media['id']) && is_array($media)) {
				$array = [];
				foreach ($media as $item) {
					if (isset($item['data'])) {
						$array[] = $this->_createMedia($item);
					} else {
						$array[] = $item;
					}
				}
				$data[$field] = $array;
			} else if (isset($media['data'])) {
				$data[$field] = $this->_createMedia($media);
			} else if (!isset($media)) {
				$association = Inflector::camelize($field);
				$association = Inflector::pluralize($association);
				$foreignKey = $this->table()->getAssociation($association)->getForeignKey();
				if ($data->offsetExists($foreignKey)) {
					$data[$foreignKey] = NULL;
				}
				if ($data->offsetExists($field)) {
					$data->offsetUnset($field);
				}
			}
		}
	}

	private function _createMedia(array $media)
	{
		$file = Media::createFromRawFile($media);
		$media = ([
			'code' => uniqid(),
			'filename' => $file['name'],
			'filepath' => $file['path'],
			'filesize' => $file['size'],
			'filetype' => $file['type'],
		]);
		return $media;
	}
}
