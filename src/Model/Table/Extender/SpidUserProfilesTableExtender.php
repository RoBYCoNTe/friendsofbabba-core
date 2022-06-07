<?php

namespace FriendsOfBabba\Core\Model\Table\Extender;

use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Table\BaseTable;
use FriendsOfBabba\Core\Model\Table\BaseTableExtender;

/**
 * Extends \FriendsOfBabba\Core\Model\Table\UserProfilesTable adding
 * required behavior specs necessary to handle additional fields required
 * to handle SPID data.
 */
class SpidUserProfilesTableExtender extends BaseTableExtender
{
	/**
	 * Initialize custom behaviors necessary to work with SPID data.
	 *
	 * @param BaseTable $baseTable
	 * @return void
	 */
	public function afterInitialize(BaseTable $baseTable, array $config): void
	{
		$baseTable->addBehavior('FriendsOfBabba/Core.Date', ['birth_date']);
	}

	/**
	 * Add required validation rules required to accept SPID signup data.
	 *
	 * @param Validator $validator
	 * @return void
	 */
	public function validationDefault(Validator $validator): void
	{
		$validator->notEmptyString('name');
		$validator->notEmptyString('surname');
		$validator->notEmptyString('phone');
	}
}
