<?php

namespace FriendsOfBabba\Core\Security;

use Cake\Utility\Hash;
use FriendsOfBabba\Core\Model\Entity\User;

/**
 * An easy way to standardize access to generated authentication data when
 * working with callbacks.
 *
 * @property array $_json
 * @property User $_user
 */
class LoginData
{
	public function __construct(User $user, array $json)
	{
		$this->_user = $user;
		$this->_json = $json;
	}

	public function isSuccess()
	{
		return Hash::get($this->_json, "success", FALSE);
	}

	public function getToken()
	{
		return Hash::get($this->_json, "data.token", NULL);
	}

	public function setData(string $path, mixed $value)
	{
		$this->_json = Hash::insert($this->_json, $path, $value);
	}

	/**
	 * Returns current json value with (optional) edits handled inside
	 * this object.
	 *
	 * @return array
	 */
	public function getJson()
	{
		return $this->_json;
	}

	/**
	 * Returns logged-in basic user's data.
	 *
	 * @return \FriendsOfBabba\Core\Model\Entity\User
	 */
	public function getUser()
	{
		return $this->_user;
	}
}
