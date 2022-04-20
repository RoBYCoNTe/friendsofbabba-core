<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Crud\Error\Exception\ValidationException;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Model\Table\TransactionsTable;
use FriendsOfBabba\Core\PluginManager;

/**
 * @property TransactionsTable $Transactions
 */
abstract class WorkflowBase
{
    const TRANSACTIONS_ENTITY_NAME = "transactions";
    /**
     * List of states handled by the workflow.
     *
     * @var State[]
     */
    private $_states = [];

    /**
     * Initialize the workflow.
     */
    public function __construct()
    {
        $this->Transactions = TableRegistry::getTableLocator()
            ->get(PluginManager::instance()
                ->getModelFQN('Transactions'));
    }

    /**
     * Your white paper, in this method you have to implement your custom workflow
     * with your own states for your specific entity.
     *
     * @return void
     */
    public abstract function init();

    /**
     * @return State[]
     *  List of states handled by the workflow.
     */
    public function getStates(): array
    {
        return $this->_states;
    }

    /**
     * Set the state of the workflow.
     *
     * @param State[] $states
     *  List of states handled by the workflow.
     */
    public function setStates(array $states)
    {
        $this->_states = $states;
    }

    /**
     * Add a state to the workflow.
     *
     * @param State $state
     *  The state to add.
     * @return State
     *  The added state.
     */
    public function addState(State $state): State
    {
        $this->_states[$state->code] = $state;
        return $state;
    }

    /**
     * Obtain list of sates readable by the user.
     *
     * @param User $user
     *  The user for which to obtain the list of states.
     * @return State[]
     *  List of states readable by the user.
     */
    public function getReadableStates(User $user): array
    {
        $roles = array_map(function ($role) {
            return $role->code;
        }, $user->roles);
        $readable = [];
        foreach ($this->_states as $code => $state) {
            foreach ($state->permissions as $permission) {
                if (in_array($permission->role, $roles) && ($permission->canRead === true || $permission->canEdit === true)) {
                    $readable[] = $code;
                }
            }
        }
        return $readable;
    }

    /**
     * Check if the user can create entities for this workflow.
     *
     * @param User $user
     *  The user for which to check if he can create entities.
     * @return boolean
     *  TRUE if the user can create entities, FALSE otherwise.
     */
    public function canCreate(User $user): bool
    {
        return $this->can('create', $user);
    }

    /**
     * Check if the user can read entities in given state.
     *
     * @param User $user
     *  The user for which to check if he can read entities.
     * @param String $state
     *  The state for which to check if he can read entities.
     * @return boolean
     *  TRUE if the user can read entities, FALSE otherwise.
     */
    public function canRead(User $user, String $state): bool
    {
        return $this->can('read', $user, $state);
    }

    /**
     * Check if the user can move entities in given state.
     *
     * @param User $user
     *  The user for which to check if he can move entities.
     * @param String $state
     *  The state for which to check if he can move entities.
     * @return boolean
     *  TRUE if the user can move entities, FALSE otherwise.
     */
    public function canMove(User $user, String $state): bool
    {
        return $this->can('move', $user, $state);
    }

    /**
     * Check if the user can edit entities in given state.
     * This permission is checked before save of the entity too.
     *
     * @param User $user
     *  The user for which to check if he can edit entities.
     * @param String $state
     *  The state for which to check if he can edit entities.
     * @return boolean
     *  TRUE if the user can edit entities, FALSE otherwise.
     */
    public function canEdit(User $user, String $state): bool
    {
        return $this->can('edit', $user, $state);
    }

    /**
     * Process beforePaginate event applying workflow rules if necessary.
     * In this step the base workflow add "hasOne" relation to transaction,
     * then it checks if the user can read the transaction and if so, it is
     * included in the list of entities to be returned.
     *
     * @param String $entityName
     *  The name of the entity to be paginated.
     * @param User $user
     *  The user for which to check if he can read entities.
     * @param Event $event
     *  The event to be processed.
     * @return void
     */
    public function beforePaginate(String $entityName, User $user, Event $event): void
    {
        if ($entityName === WorkflowBase::TRANSACTIONS_ENTITY_NAME) {
            // This entity has to be handled by developers.
            return;
        }
        $states = $this->getReadableStates($user);
        $transactionEntity = Inflector::singularize($entityName) . "Transactions";
        $table = TableRegistry::getTableLocator()->get($entityName);
        $table->hasOne($transactionEntity, [
            'foreignKey' => 'record_id',
            'propertyName' => 'transaction',
            'dependent' => true,
            'conditions' => ["$transactionEntity.is_current" => true]
        ]);

        $query = $event->getSubject()->query;
        $query->contain([$transactionEntity]);
        $query->where(["$transactionEntity.state in" => $states]);
    }

    /**
     * Process beforeSave event applying workflow rules if necessary.
     *
     * @param String $entityName
     *  The name of the entity to be saved.
     * @param User $user
     *  The user for which to check if he can edit entities.
     * @param Event $event
     *  The event to be processed.
     * @return void
     */
    public function afterPaginate(String $entityName, User $user, Event $event): void
    {
        return;
    }

    /**
     * Process beforeFind event applying workflow rules if necessary.
     * This event is fired when user try to view the entity.
     * The hasOne relation with transaction is added.
     *
     * @param String $entityName
     *  The name of the entity to be found.
     * @param User $user
     *  The user for which to check if he can read entities.
     * @param Event $event
     *  The event to be processed.
     * @return void
     */
    public function beforeFind(String $entityName, User $user, Event $event): void
    {
        $id = $event->getSubject()->id;
        $last = $this->Transactions->getLast($id, $entityName);
        if (empty($last)) {
            throw new UnauthorizedException(__d("workflow", 'You are not authorized to view this record because it\'s state is corrupted.'));
        }

        $canRead = $this->canRead($user, $last->state);
        if (!$canRead) {
            throw new ForbiddenException(__d("workflow", "Forbidden"));
        }

        $table = TableRegistry::getTableLocator()->get($entityName);
        $transactionEntity = Inflector::singularize($entityName) . "Transactions";
        $table->hasOne($transactionEntity, [
            'foreignKey' => 'record_id',
            'propertyName' => 'transaction',
            'dependent' => true,
            'conditions' => ["$transactionEntity.is_current" => true]
        ]);
        $query = $event->getSubject()->query;
        $query->contain([$transactionEntity]);
    }

    /**
     * Process beforeSave event applying workflow rules if necessary.
     * The workflow will check if user has edit permission on last state.
     *
     * @param String $entityName
     *  The name of the entity to be saved.
     * @param User $user
     *  The user for which to check if he can edit entities.
     * @param Event $event
     *  The event to be processed.
     * @return void
     */
    public function beforeSave(String $entityName, User $user, Event $event): void
    {
        $entity = $event->getSubject()->entity;
        $last = !is_null($entity->id) ? $this->Transactions->getLast($entity->id, $entityName) : NULL;
        $lastState = !empty($last) ? $this->getState($last->state) : $this->getInitial();
        if (empty($entity->state)) {
            $entity->state = $lastState->code;
        }
        $moved = FALSE;

        if (empty($lastTransaction)) {
            $canCreate = $this->canCreate($user);
            if (!$canCreate) {
                throw new ForbiddenException(__d("workflow", "Forbidden"));
            }
            $moved = TRUE;
        } else {
            $moved = !empty($entity->state) && $lastTransaction->state !== $entity->state;
        }

        $next = $entity->state;

        if (!empty($last) && (is_null($next) || (!is_null($last) && $last->state === $next))) {
            $canEdit = $this->canEdit($user, $last->state);
            if (!$canEdit) {
                throw new ForbiddenException(__d("workflow", "Forbidden"));
            }
            $next = $last->state;
        } else {
            $canMove = $this->canMove($user, $next);
            if (!$canMove) {
                throw new ForbiddenException(__d("workflow", "Forbidden"));
            }
        }

        if ($lastState->hasTransitionTo($next)) {
            $route = $lastState->getTransitionTo($next);
            if ($route->notesRequired && (is_null($entity->notes) || empty($entity->notes))) {
                $entity->setError('notes', __('Required.'));
                throw new ValidationException($entity);
            }
        }

        $next = $this->getState($next);

        $event->getSubject()->moved = $moved;

        $workflowEvent = WorkflowEvent::create($event, $user, $event->getSubject()->moved);
        $workflowEvent = $next->beforeSave($workflowEvent);

        $event->getSubject()->bag = $workflowEvent->getBag();

        if (!$workflowEvent->success) {
            throw new BadRequestException($workflowEvent->message);
        }
    }

    /**
     * Save transaction after successful save.
     *
     * @param String $entityName
     *  The name of the entity to be saved.
     * @param User $user
     *  The user for which to check if he can edit entities.
     * @param Event $event
     *  The event to be processed.
     * @return void
     */
    public function afterSave(String $entityName, User $user, Event $event): void
    {
        /** @var Entity $entity */
        $entity = $event->getSubject()->entity;
        if ($entity->hasErrors()) {
            return;
        }
        $last = $this->Transactions->getLast($entity->id, $entityName);
        $nextStateCode = $entity->state;
        if (is_null($nextStateCode)) {
            $nextStateCode = $last->state;
        }

        $nextState = $this->getState($nextStateCode);
        if ($nextState->isLoop) {
            $nextStateCode = $last->state;
        }

        if (!empty($last) && !$nextState->isLoop) {
            $last->is_current = false;
            $this->Transactions->forEntity($entityName)->save($last);
        }

        $transaction = $this->Transactions
            ->forEntity($entityName)
            ->newEntity([]);
        $transaction->record_id = $entity->id;
        $transaction->state = $nextState->isLoop ? $nextState->code : $nextStateCode;
        $transaction->user_id = $user->id;
        $transaction->is_current = !$nextState->isLoop;
        $transaction->is_private = Hash::get($entity, "is_private", FALSE);
        $transaction->notes = Hash::get($entity, "notes", NULL);

        $transaction->data = json_encode(array_merge($entity->toArray(), [
            'transaction' => NULL
        ]));
        if (!$this->Transactions->forEntity($entityName)->save($transaction)) {
            throw new BadRequestException(__d(
                "workflow",
                "Error saving transaction: {0}",
                json_encode($transaction->getErrors())
            ));
        }

        $workflowEvent = WorkflowEvent::create($event, $user, $event->getSubject()->moved);
        $workflowEvent->setBag($event->getSubject()->bag);
        $nextState->afterSave($workflowEvent);
    }

    /**
     * Get state by code.
     *
     * @param String $code
     *  The code of the state to be retrieved.
     * @return State
     *  The state with the given code.
     */
    public function getState(String $code): State
    {
        return $this->_states[$code];
    }

    /**
     * Get initial state.
     *
     * @return State
     */
    public function getInitial(): State
    {
        $state = array_filter($this->_states, function ($state) {
            return $state->isInitial === true;
        });
        if (empty($state)) {
            return false;
        }
        return array_values($state)[0];
    }

    private function can($permission, $user, $stateCode = null): bool
    {
        $can = 'can' . Inflector::camelize($permission);
        $roles = array_map(function ($role) {
            return $role->code;
        }, $user->roles);

        if (!is_null($stateCode)) {
            $state = $this->_states[$stateCode];
        } else {
            $state = $this->getInitial();
        }

        $permissions = array_filter($state->permissions, function ($permission) use ($can) {
            return $permission->$can === true;
        });
        $allowedRoles = array_map(function ($permission) {
            return $permission->role;
        }, $permissions);

        $allowed = false;
        foreach ($roles as $userRole) {
            if (in_array($userRole, $allowedRoles)) {
                $allowed = true;
            }
        }

        return $allowed;
    }

    public function toArray(): array
    {
        return [
            'states' => array_values($this->getStates())
        ];
    }
}
