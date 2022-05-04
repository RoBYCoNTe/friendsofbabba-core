<?php

namespace FriendsOfBabba\Core\Model\Crud;

class TabbedForm extends Form
{
	public function __construct()
	{
		parent::__construct();
		parent::setComponent("TabbedForm");
		parent::setComponentProp("tabs", []);
	}

	public function addTab(FormTab $form): TabbedForm
	{
		$tabs = $this->getComponentProp("tabs");
		$tabs[] = $form;
		$this->setComponentProp("tabs", $tabs);
		return $this;
	}

	public function add(string $label): FormTab
	{
		$tab = FormTab::create($label);
		$tabs = $this->getComponentProp("tabs");
		$tabs[] = $tab;
		$this->setComponentProp("tabs", $tabs);

		return $tab;
	}
}
