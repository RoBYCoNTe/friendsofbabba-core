<?php

namespace FriendsOfBabba\Core\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use FriendsOfBabba\Core\Model\Crud\Badge;
use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\Grid;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Extender;

abstract class BaseTableExtender implements Extender
{
	public function getKind(): string
	{
		return Extender::TABLE;
	}

	public function beforeInitialize(BaseTable $baseTable, array $config): void
	{
	}

	public function afterInitialize(BaseTable $baseTable, array $config): void
	{
	}

	public function getForm(Form $form, User $user): void
	{
	}

	public function getGrid(Grid $grid, User $user): void
	{
	}

	public function validationDefault(Validator $validator): void
	{
	}

	public function getBadge(BaseTable $baseTable, User $user): ?Badge
	{
		return NULL;
	}

	public function buildRules(RulesChecker $rules): void
	{
	}
}
