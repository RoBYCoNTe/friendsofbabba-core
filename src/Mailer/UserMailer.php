<?php

namespace FriendsOfBabba\Core\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use FriendsOfBabba\Core\Model\Entity\User;

class UserMailer extends Mailer
{
	public function password(User $user, string $newPassword): Mailer
	{
		$mail = $this
			->setEmailFormat("html")
			->setTo($user->email)
			->setSubject(__d("friendsofbabba_core", "Password reset"))
			->setViewVars([
				'user' => $user->name,
				'newPassword' => $newPassword,
				'dashboard' => Configure::read('App.dashboard', '/dashboard/index.html#/')
			]);

		$mail->viewBuilder()
			->setTemplate("FriendsOfBabba/Core.password")
			->setLayout("FriendsOfBabba/Core.layout");

		return $mail;
	}
}
