<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
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
        $this->removeInvalids();
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
     * Remove all invalid workflows found in to config/workflow.php config file.
     * Just one invalid workflow can cause the whole registry to be invalid.
     * We have to mantain the registry valid.
     */
    public function removeInvalids(): void
    {
        $phpConfig = new PhpConfig(CONFIG);
        if (!file_exists(CONFIG . 'workflow.php')) {
            file_put_contents(CONFIG . 'workflow.php', '<?php' . PHP_EOL . 'return [];');
        }
        $config = $phpConfig->read('workflow');
        $workflows = &$config['workflow'];
        foreach ($workflows as $entity => $workflow) {
            if (!class_exists($workflow)) {
                unset($workflows[$entity]);
            }
        }
        $phpConfig->dump('workflow', $config);
    }


    /**
     * Add new entity to list of configured entities.
     *
     * @param string $entity
     *  Name of the entity to be added.
     * @return void
     */
    public function add(string $entity): void
    {
        $phpConfig = new PhpConfig(CONFIG);
        if (!file_exists(CONFIG . 'workflow.php')) {
            file_put_contents(CONFIG . 'workflow.php', '<?php' . PHP_EOL . 'return [];');
        }
        $config = $phpConfig->read('workflow');
        $config['workflow'][$entity] = "App\\Workflow\\{$entity}\\Workflow";
        $phpConfig->dump('workflow', $config);
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
    public function register(string $entity, WorkflowBase $workflow): void
    {
        $this->_configured[$entity] = $workflow;
        $this->_configured[$entity]->init();
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
