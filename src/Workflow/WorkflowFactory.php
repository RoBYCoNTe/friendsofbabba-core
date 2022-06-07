<?php

namespace FriendsOfBabba\Core\Workflow;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Represents the root access to every configured workflow for the application.
 */
class WorkflowFactory
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
        $debug = Configure::read('debug');
        if ($debug === TRUE) {
            $this->removeInvalids();
        }
        $this->init();
    }

    /**
     * Get the singleton instance of the WorkflowFactory.
     *
     * @return WorkflowFactory
     */
    public static function instance(): WorkflowFactory
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new WorkflowFactory();
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
        if (empty($config['workflow'])) {
            $config['workflow'] = [];
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
     * Get full list of configured workflows.
     *
     * @return array
     */
    public function getConfigured(): array
    {
        return $this->_configured;
    }

    /**
     * Returns the same list as getConfigured() but with the resource dashed name as key.
     *
     * @return array
     */
    public function getConfiguredAsResources(): array
    {
        $configured = $this->getConfigured();
        $output = [];
        foreach ($configured as $entity => $workflow) {
            $output[Inflector::dasherize($entity)] = $workflow;
        }
        return $output;
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
