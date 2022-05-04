<?php

namespace FriendsOfBabba\Core\Model\Crud;

class Badge
{
	public mixed $value;
	public string $color;
	public string $icon;
	public string $variant;
	public bool $show;


	public function __construct(string $color, mixed $value)
	{
		$this->color = $color;
		$this->value = $value;
	}

	public static function create(string $color, string $value): Badge
	{
		return new Badge($color, $value);
	}
	public static function secondary(string $value): Badge
	{
		return self::create("secondary", $value);
	}

	public static function primary(string $value): Badge
	{
		return self::create("primary", $value);
	}

	public static function error(string $value): Badge
	{
		return self::create("error", $value);
	}

	public function dot(): Badge
	{
		$this->variant = "dot";
		$this->value = " ";
		return $this;
	}

	public function hide($hide = TRUE): Badge
	{
		$this->show = !$hide;
		return $this;
	}

	public function visible($visible): Badge
	{
		$this->show = $visible;
		return $this;
	}
}
