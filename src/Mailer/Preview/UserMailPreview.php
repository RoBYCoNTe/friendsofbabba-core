<?php

namespace FriendsOfBabba\Core\Mailer\Preview;

use Cake\Utility\Text;
use DebugKit\Mailer\MailPreview;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Entity\UserProfile;

class UserMailPreview extends MailPreview
{
	public function password()
	{
		$user = new User([
			'email' => 'roberto.conterosito@gmail.com',
			'profile' => new UserProfile([
				'name' => 'Roberto',
				'surname' => 'Conte Rosito'
			])
		]);
		$newPassword = substr(Text::uuid(), 0, 8);
		return $this->getMailer('FriendsOfBabba/Core.User')->password($user, $newPassword);
	}
}
