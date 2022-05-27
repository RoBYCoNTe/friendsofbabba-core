<?php

namespace FriendsOfBabba\Core\Service;

use FriendsOfBabba\Core\Model\Entity\User;

/**
 * Define basic interface to customize user data based on action.
 * Implementing this interface your can personalize login and profile data
 * to expose to client while executing REST API.
 */
interface UserServiceInterface
{
	/**
	 * Prepare login data after successful login.
	 *
	 * @param User $user
	 * 	User for which prepare login data.
	 * @param array $data
	 *  Addiitional data to be merged with login data.
	 * @return array
	 *  Return array with login data.
	 */
	function getLogin(User $user, array $data): array;

	/**
	 * Prepare profile data when user want to view or change his profile.
	 *
	 * @param User $user
	 *  User for which prepare profile data.
	 * @return array
	 *  Return array with profile data.
	 */
	function getProfile(User $user): array;

	/**
	 * Prepare login data after successful impersonation request.
	 *
	 * @param User $user
	 * 	 User for which prepare login data.
	 * @param array $data
	 *  Addiitional data to be merged with login data.
	 * @return array
	 *  Return array with login data.
	 */
	function getImpersonate(User $user, array $data): array;
}
