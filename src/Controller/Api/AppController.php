<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Authentication\Authenticator\UnauthenticatedException;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Component\RequestHandlerComponent;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Crud\Controller\Component\CrudComponent;
use Crud\Controller\ControllerTrait;
use FriendsOfBabba\Core\Controller\Component\NotificationComponent;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\UsersTable;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/**
 * App Controller
 *
 * @property CrudComponent $Crud
 * @property RequestHandlerComponent $RequestHandler
 * @property AuthenticationComponent $Authentication
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
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Delete',
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

        $prefix = strtolower($subject->request->getParam("prefix"));
        $method = $subject->request->getEnv("REQUEST_METHOD");
        $action = $method . " /$prefix/$controller/$action";

        $user = $this->getUser();
        if (is_null($user)) {
            throw new UnauthorizedException(__d("fob", "User not authenticated or session expired!"));
        }
        if (!$user->hasPermission($action)) {
            throw new UnauthorizedException(__d("fob", "Unauthorized: {0}", $action));
        }
    }

    /**
     * Returns information of the connected user.
     *
     * @return ?User
     */
    public function getUser(bool $throws = true): ?User
    {
        $result = $this->Authentication->getResult();
        if (!$result->isValid()) {
            if ($throws) {
                throw new UnauthenticatedException("User not authenticated or session expired!");
            }
            return NULL;
        }

        $user = $result->getData();

        $modelName = PluginManager::getInstance()->getFQN('Users');

        /** @var UsersTable */
        $repository = TableRegistry::getTableLocator()->get($modelName);

        $query = $repository->find()
            ->contain([
                'Roles',
                'Roles.RolePermissions'
            ])
            ->where(['Users.id' => $user->id]);

        $user = $query->first();
        return $user;
    }

    public function useModel(string $model): void
    {
        $this->Crud->useModel($model);
        $this->modelClass = $model;
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

    public function _beforePaginate(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforePaginate($entityName, $user, $event);
        }
    }

    public function _beforeFind(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeFind($entityName, $user, $event);
        }
    }

    public function _beforeSave(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeSave($entityName, $user, $event);
        }
    }

    public function _beforeDelete(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->beforeDelete($entityName, $user, $event);
        }
    }

    public function _afterSave(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->afterSave($entityName, $user, $event);
        }
    }

    public function _afterPaginate(\Cake\Event\Event $event)
    {
        $entityName = $this->request->getParam("controller");
        $workflow = WorkflowRegistry::getInstance()->resolve($entityName);
        if (!is_null($workflow)) {
            $user = $this->getUser();
            $workflow->afterPaginate($entityName, $user, $event);
        }
    }
}
