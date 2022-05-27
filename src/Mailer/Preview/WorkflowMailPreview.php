<?php

namespace FriendsOfBabba\Core\Mailer\Preview;

use DebugKit\Mailer\MailPreview;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Entity\UserProfile;

class WorkflowMailPreview extends MailPreview
{
	public function notify()
	{
		$user = new User([
			'email' => 'roberto.conterosito@gmail.com',
			'profile' => new UserProfile([
				'name' => 'Roberto',
				'surname' => 'Conte Rosito'
			])
		]);
		return $this->getMailer('FriendsOfBabba/Core.Workflow')->notify(
			$user,
			'New message sent.',
			'This is a test message.',
			'notifications/1'
		);
	}
}
