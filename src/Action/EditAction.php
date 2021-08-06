<?php

namespace FriendsOfBabba\Core\Action;

use Cake\Http\Response;
use Crud\Action\EditAction as BaseEditAction;
use Crud\Event\Subject;

class EditAction extends BaseEditAction
{
	protected function _success(Subject $subject): ?Response
	{
		$subject->set([
			'success' => true,
			'created' => false
		]);
		$this->_trigger('afterSave', $subject);
		$this->setFlash('success', $subject);
		$this->setConfig('serialize.data', 'entity');
		$this->_controller()->set('entity', $subject->entity);

		return $this->_redirect($subject, ['action' => 'index']);
	}
}
