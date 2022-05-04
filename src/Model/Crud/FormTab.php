<?php

namespace FriendsOfBabba\Core\Model\Crud;

class FormTab extends Form
{
	public function __construct()
	{
		parent::__construct();
		parent::setComponent("FormTab");
	}

	public function setLabel(string $label): FormTab
	{
		parent::setComponentProp("label", $label);
		return $this;
	}

	public static function create(string $label): FormTab
	{
		$formTab = new FormTab();
		$formTab->setLabel($label);
		return $formTab;
	}
}
