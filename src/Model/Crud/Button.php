<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Button extends Component
{
	public function __construct(string $component, array $props = [])
	{
		parent::__construct($component, $props);
	}

	public static function create(string $component, array $props = [])
	{
		return new Button($component, $props);
	}
}
