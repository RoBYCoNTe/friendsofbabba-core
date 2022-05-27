<?php

namespace FriendsOfBabba\Core\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Mailer;
use FriendsOfBabba\Core\Model\Entity\User;

class WorkflowMailer extends Mailer
{
	public function notify(
		User $user,
		string $subject,
		string $content,
		?string $resource = NULL
	): Mailer {
		$mail = $this
			->setEmailFormat("html")
			->setTo($user->email)
			->setSubject($subject)
			->setViewVars([
				'user' => $user,
				'content' => $content,
				'resource' => $resource,

				'appName' => Configure::read('App.name'),
				'dashboard' => Configure::read('App.dashboard', '/dashboard/index.html#/')
			]);
		$mail->viewBuilder()
			->setTemplate("FriendsOfBabba/Core.notify")
			->setLayout("FriendsOfBabba/Core.layout");

		return $mail;
	}
}
