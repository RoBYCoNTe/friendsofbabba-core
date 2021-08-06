<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Authentication\Authenticator\UnauthenticatedException;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Component\RequestHandlerComponent;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Crud\Controller\Component\CrudComponent;
use Crud\Controller\ControllerTrait;
use FriendsOfBabba\Core\Controller\Component\NotificationComponent;
use FriendsOfBabba\Core\Hook\HookManager;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\UsersTable;
use FriendsOfBabba\Core\PluginManager;

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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $hookName = PluginManager::instance()->getHookFQN('Controller/Api/App.beforeFilter');
        $return = HookManager::instance()->fire($hookName, $event);
        if ($return === true) {
            return;
        }

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
     * @return User
     */
    public function getUser()
    {
        $result = $this->Authentication->getResult();
        if (!$result->isValid()) {
            throw new UnauthenticatedException(__d('fob', 'Unable to authenticate user.'));
        }
        $user = $result->getData();

        $modelName = PluginManager::instance()->getModelFQN('Users');

        /** @var UsersTable */
        $repository = TableRegistry::getTableLocator()->get($modelName);

        $query = $repository->find()
            ->contain([
                'Roles',
                'Roles.RolePermissions'
            ])
            ->where(['Users.id' => $user->id]);

        $hookName = 'Controller/Api/App.getUser';
        $queryOverride = HookManager::instance()->fire($hookName, $query, $user);

        if (!is_null($queryOverride) && $queryOverride instanceof Query) {
            $query = $queryOverride;
        }

        $user = $query->first();
        return $user;
    }
}
