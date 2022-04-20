<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Component
{

	/**
	 * Get component to be used.
	 *
	 * @var string
	 */
	public string $component;

	/**
	 * Get component props
	 *
	 * @var array
	 */
	public array $componentProps;

	public function __construct(string $component, array $props = [])
	{
		$this->component = $component;
		$this->props = $props;
	}

	public function setComponentProp(string $name, mixed $value = NULL): Component
	{
		if (is_null($name)) {
			unset($this->componentProps[$name]);
		} else {
			$this->componentProps[$name] = $value;
		}
		return $this;
	}

	public function getComponentProp(string $name): mixed
	{
		return $this->componentProps[$name] ?? NULL;
	}


	public function setComponent(string $component): Component
	{
		$this->component = $component;
		return $this;
	}
}
