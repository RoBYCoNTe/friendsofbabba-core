<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Command\Workflow;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Inflector;
use FriendsOfBabba\Core\PluginManager;
use FriendsOfBabba\Core\Workflow\WorkflowRegistry;

/**
 * Create Workflow.
 */
class CreateFilesCommand extends Command
{
	public function initialize(): void
	{
		parent::initialize();
		$this->loadModel(PluginManager::instance()->getModelFQN('Transactions'));
	}
	/**
	 * Hook method for defining this command's option parser.
	 *
	 * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
	{
		$parser = parent::buildOptionParser($parser);
		$parser->addOption('entity', ['short' => 'e', 'required' => true]);
		$parser->addOption('namespace', ['short' => 'n', 'default' => '\\App\\Workflow']);
		$parser->addOption('states', ['short' => 's', 'required' => true, 'help' => 'List of states separated by comma', 'default' => 'Draft,Approved']);
		$parser->addOption('routes', ['short' => 'r', 'required' => true, 'help' => 'List of routes separated by comma: state1:state2']);

		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$entity = $args->getOption('entity');
		$namespace = $args->getOption('namespace');

		$states = $args->getOption('states');
		$states = explode(',', $states);
		$states = array_filter($states);

		$routes = $args->getOption('routes');
		$routes = explode(',', $routes);
		$routes = array_filter($routes);

		$this->_createNamespaceFolder($namespace, $entity);
		$this->_createStateFiles($namespace, $entity, $states);
		$this->_createWorkflowFile($namespace, $entity, $states, $routes);

		$this->_registerWorkflow($namespace, $entity);
	}

	private function _registerWorkflow(string $namespace, string $entity): void
	{
		$phpConfig = new PhpConfig(CONFIG);
		if (!file_exists(CONFIG . 'workflow.php')) {
			file_put_contents(CONFIG . 'workflow.php', '<?php' . PHP_EOL . 'return [];');
		}
		$config = $phpConfig->read('workflow');
		$config['workflow'][$entity] = "{$namespace}\\{$entity}\\Workflow";
		$phpConfig->dump('workflow', $config);

		// Validate the workflow.
		WorkflowRegistry::getInstance()->validate();
	}

	private function _createWorkflowFile(string $namespace, string $entity, array $states, array $routes)
	{
		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		$ns = substr($namespace, 1);
		$template = file_get_contents(ROOT . DS . "plugins/FriendsOfBabba/Core/src/Workflow/Template/Workflow.template.php");
		$template = str_replace("namespace FriendsOfBabba\Core\Workflow\Template;", "namespace $ns\\$entity;", $template);

		$stateNamespaces = array_map(function ($state) use ($ns, $entity) {
			return "use $ns\\$entity\\States\\$state;";
		}, $states);

		$template = str_replace("// __USE_STATE_NAMESPACES__", implode("\n", $stateNamespaces), $template);

		$states = array_map(function ($state, $index) {
			return ($index > 0 ?  "\t\t" : "") . "\$this->addState(new $state());";
		}, $states, array_keys($states));
		$states = implode(PHP_EOL, $states);

		if (!empty($routes)) {
			$routes = array_map(function ($route) {
				$route = explode(':', $route);
				$from = Inflector::camelize($route[0]);
				$to = Inflector::camelize($route[1]);
				return "\t\t\$this->getState({$from}::CODE)->addRoute(\$this->getState({$to}::CODE));";
			}, $routes);
			$routes = implode(PHP_EOL, $routes);
		}


		$init = implode(PHP_EOL, [
			$states,
			PHP_EOL,
			"\t\t// Routes:",
			PHP_EOL,
			$routes
		]);

		$template = str_replace("// __INIT__", $init, $template);

		file_put_contents($namespaceFolder . DS . "Workflow.php", $template);
	}

	private function _createStateFiles(string $namespace, string $entity, array $states): void
	{
		foreach ($states as $state) {
			$this->_createStateFile($namespace, $entity, $state);
		}
	}

	private function _createStateFile(string $namespace, string $entity, string $stateName): void
	{
		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		$ns = substr($namespace, 1);
		$template = file_get_contents(ROOT . DS . "plugins/FriendsOfBabba/Core/src/Workflow/Template/State.template.php");
		$template = str_replace("namespace FriendsOfBabba\Core\Workflow\Template\States;", "namespace $ns\\$entity\\States;", $template);
		$template = str_replace("class StateTemplate", "class $stateName", $template);
		$template = str_replace("// __STATE_LABEL__", Inflector::humanize($stateName), $template);
		$template = str_replace("// __STATE_NAME__", $stateName, $template);
		$template = str_replace("// __STATE_CODE__", Inflector::dasherize($stateName), $template);

		file_put_contents($namespaceFolder . DS . "States" . DS . $stateName . ".php", $template);
	}

	private function _getNamespaceFolder(string $namespace, string $entity): string
	{
		$path = explode('\\', str_replace('\\App\\', '', $namespace));
		$path = implode("/", $path);
		$path = APP . $path . DS . $entity;
		return $path;
	}

	private function _createNamespaceFolder(string $namespace, string $entity)
	{
		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		if (!file_exists($namespaceFolder)) {
			mkdir($namespaceFolder, 0777, true);
		}
		if (!file_exists($namespaceFolder . DS . "States")) {
			mkdir($namespaceFolder . DS . "States", 0777, true);
		}

		return $namespaceFolder;
	}
}
