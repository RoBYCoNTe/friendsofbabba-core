<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\Controller\Component\AuthorizationComponent;
use Cake\Controller\Component\RequestHandlerComponent;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Inflector;
use Crud\Controller\Component\CrudComponent;
use Crud\Controller\ControllerTrait;
use FriendsOfBabba\Core\Controller\Component\NotificationComponent;
use FriendsOfBabba\Core\ExtenderFactory;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Workflow\WorkflowBase;
use FriendsOfBabba\Core\Workflow\WorkflowFactory;

/**
 * App Controller
 *
 * @property CrudComponent $Crud
 * @property RequestHandlerComponent $RequestHandler
 * @property AuthenticationComponent $Authentication
 * @property AuthorizationComponent $Authorization
 * @property NotificationComponent $Notification
 */
class AppController extends Controller
{
    use ControllerTrait;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('FriendsOfBabba/Core.Notification');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Delete',
                'deleteAll' => [
                    'className' => \FriendsOfBabba\Core\Action\Bulk\DeleteAction::class
                ],
                'edit' => [
                    'className' => \FriendsOfBabba\Core\Action\EditAction::class
                ]
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog',
                'Crud.Search'
            ]
        ]);
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $subject = $event->getSubject();
        $action = $subject->request->getParam("action");
        $unauthenticatedActions = $this->Authentication->getUnauthenticatedActions();
        if (in_array($action, $unauthenticatedActions)) {
            return;
        }
        $action = Inflector::dasherize($action);

        if (in_array($action, $unauthenticatedActions)) {
            return;
        }
        $action = strtolower($action);
        if (in_array($action, $unauthenticatedActions)) {
            return;
        }

        $controller = strtolower(Inflector::dasherize($subject->request->getParam("controller")));
        $user = $this->getUser();
        if (is_null($user)) {
            throw new UnauthorizedException(__d('friendsofbabba_core', "User not authenticated or session expired!"));
        }

        $prefix = strtolower($subject->request->getParam("prefix"));
        $method = $subject->request->getEnv("REQUEST_METHOD");
        $permission = $method . " /$prefix/$controller/$action";

        if (!$user->hasPermission($permission)) {
            throw new UnauthorizedException(__d('friendsofbabba_core', "Unauthorized: {0}", $action));
        }

        $actionName = "before" . Inflector::camelize($action);
        $entityName = $this->request->getParam("controller");

        ExtenderFactory::instance()->fireAction($entityName,  $actionName, $event, $this);
    }

    /**
     * Returns information of the connected user.
     *
     * @return ?User
     */
    public function getUser(): ?User
    {
        return $this->request->getAttribute('identity');
    }

    public function useModel(string $model): void
    {
        $this->Crud->useModel($model);
    }

    public function implementedEvents(): array
    {
        return parent::implementedEvents() + [
            'Crud.beforePaginate' => '_beforePaginate',
            'Crud.beforeFind' => '_beforeFind',
            'Crud.beforeSave' => '_beforeSave',
            'Crud.beforeDelete' => '_beforeDelete',
            'Crud.afterSave' => '_afterSave',
            'Crud.afterPaginate' => '_afterPaginate',
        ];
    }

    public function getResourceName(): string
    {
        $matchedRoute = $this->request->getParam('_matchedRoute');
        $resourceName = explode('/', $matchedRoute);
        $resourceName = array_pop($resourceName);
        $resourceName = Inflector::camelize($resourceName, "-");
        return $resourceName;
    }

    public function getEntityName(): string
    {
        return $this->request->getParam("controller");
    }

    public function resolveWorkflow(): ?WorkflowBase
    {
        $resourceName = $this->getResourceName();
        $entityName = $this->getEntityName();
        $possibleMatches = array_unique([$resourceName, $entityName]);
        foreach ($possibleMatches as $possibleMatch) {
            $workflow = WorkflowFactory::instance()->resolve($possibleMatch);
            if (!is_null($workflow)) {
                return $workflow;
            }
        }
        return null;
    }

    public function _beforePaginate(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforePaginate($entityName, $user, $event);
        }
        ExtenderFactory::instance()->beforePaginate($entityName, $event, $this);
    }

    public function _beforeFind(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeFind($entityName, $user, $event);
        }
        ExtenderFactory::instance()->beforeFind($entityName, $event, $this);
    }

    public function _beforeSave(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeSave($entityName, $user, $event);
        }
        ExtenderFactory::instance()->beforeSave($entityName, $event, $this);
    }

    public function _afterSave(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->afterSave($entityName, $user, $event);
        }
        ExtenderFactory::instance()->afterSave($entityName, $event, $this);
    }


    public function _beforeDelete(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeDelete($entityName, $user, $event);
        }
        ExtenderFactory::instance()->beforeDelete($entityName, $event, $this);
    }

    public function _afterPaginate(\Cake\Event\Event $event)
    {
        $workflow = $this->resolveWorkflow();
        $entityName = $this->getEntityName();
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->afterPaginate($entityName, $user, $event);
        }
        ExtenderFactory::instance()->afterPaginate($entityName, $event, $this);
    }

    public function order()
    {
        $id = $this->request->getData('id', 5);
        $source = $this->request->getData('source', 1);
        $destination = $this->request->getData('destination', 3);

        $repository = $this->Crud->table();
        if (!$repository->hasBehavior('Draggable')) {
            throw new \Exception("Draggable behavior not found!");
        }

        $result = $repository->moveUpDown($id, $source, $destination);

        $this->set([
            'data' => [
                'id' => $id,
                'source' => $source,
                'destination' => $destination,
                'result' => $result
            ],
            'success' => $result,
            '_serialize' => ['data', 'success'],
        ]);
    }
}
