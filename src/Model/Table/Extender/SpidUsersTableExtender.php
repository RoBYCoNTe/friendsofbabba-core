<?php

namespace FriendsOfBabba\Core\Model\Table\Extender;

use FriendsOfBabba\Core\Model\Crud\Form;
use FriendsOfBabba\Core\Model\Crud\FormInput;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\BaseTableExtender;

class SpidUsersTableExtender extends BaseTableExtender
{
	public function getForm(Form $form, User $user): void
	{
		$form->addInput(FormInput::create("profile.phone", __d("friendsofbabba_core", "Phone")), "after", "profile.surname");
		$form->addInput(FormInput::create("profile.birth_place", __d("friendsofbabba_core", "Birth Place")), "after", "profile.phone");
		$form->addInput(FormInput::create("profile.birth_province", __d("friendsofbabba_core", "Birth Province"))
			->setComponent("SmartTextInput")
			->setComponentProp("maxLength", 2), "after", "profile.birth_place");
		$form->addInput(FormInput::create("profile.birth_date", __d("friendsofbabba_core", "Birth Date"))
			->setComponent("DateInput"), "after", "profile.birth_province");
	}
}
