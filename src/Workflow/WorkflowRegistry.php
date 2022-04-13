<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Represents the root access to every configured workflow for the application.
 */
class WorkflowRegistry
{
    private static $_instance = null;

    /**
     * @var array
     */
    private $_configured = [];

    /**
     * Initialize the registry.
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Get the singleton instance of the WorkflowRegistry.
     *
     * @return WorkflowRegistry
     */
    public static function getInstance(): WorkflowRegistry
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new WorkflowRegistry();
        }
        return self::$_instance;
    }

    /**
     * Try to load the workflow configuration.
     */
    public function validate(): void
    {
        $this->init();
    }

    /**
     * Load all configured workflow in config/workflow.php
     *
     * @return void
     */
    public function init(): void
    {
        if (!file_exists(CONFIG . "workflow.php")) {
            return;
        }
        Configure::load('workflow');
        $workflow = Configure::read('workflow');
        foreach ($workflow as $entity => $workflowClass) {

            $this->_configured[$entity] = new $workflowClass();
            $this->_configured[$entity]->init();
        }
    }

    /**
     * Get the workflow for the given entity.
     *
     * @param string $entityName The name of the entity.
     * @return array
     */
    public function getConfigured()
    {
        return $this->_configured;
    }

    /**
     * Register new workflow to be used.
     *
     * @param string $entityName
     *  Name of the entity for which the workflow is configured.
     * @param WorkflowBase $workflow
     *  Instance of the workflow to be used.
     * @return void
     */
    public function register(string $entityName, WorkflowBase $workflow): void
    {
        $this->_configured[$entityName] = $workflow;
        $this->_configured[$entityName]->init();
    }

    /**
     * Resolve the workflow for the given entity.
     *
     * @param String $entityName
     *  Name of the entity for which the workflow is configured.
     * @return WorkflowBase|null
     *  Instance of the workflow to be used.
     */
    public function resolve($entityName): ?WorkflowBase
    {
        return Hash::get($this->_configured, $entityName);
    }
}
