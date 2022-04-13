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
		$parser->addArgument('entity', ['required' => true]);
		$parser->addOption('namespace', ['short' => 'n', 'default' => '\\App\\Workflow']);
		$parser->addOption('states', ['short' => 's', 'required' => true, 'help' => 'List of states separated by comma', 'default' => 'Draft,Approved']);
		$parser->addOption('routes', ['short' => 'r', 'required' => true, 'help' => 'List of routes separated by comma: state1:state2']);
		$parser->addOption('erase', ['short' => 'e', 'required' => false, 'help' => 'Erase workflow files before creation (you lost everything!)']);

		return $parser;
	}

	/**
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 * @return null|void|int The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io)
	{
		$entity = $args->getArgument('entity');
		$erase = $args->getOption('erase');

		$io->out(sprintf("\nBaking Workflow classes for %s...\n", $entity));

		$namespace = $args->getOption('namespace');

		$states = $args->getOption('states');
		$states = explode(',', $states);
		$states = array_filter($states);

		$routes = $args->getOption('routes');
		$routes = explode(',', $routes);
		$routes = array_filter($routes);

		if ($erase) {
			if ($io->askChoice('Erase workflow files before creation?', ['y', 'n'], 'n') === 'y') {
				$folder = $this->_getNamespaceFolder($namespace, $entity);
				$this->_removeDirectory($folder);

				WorkflowRegistry::getInstance()->removeInvalids();
			}
		}

		$this->_createNamespaceFolder($namespace, $entity, $io);
		$this->_createStateFiles($namespace, $entity, $states, $io);
		$this->_createWorkflowFile($namespace, $entity, $states, $routes, $io);

		WorkflowRegistry::getInstance()->add($entity);
	}

	private function _createNamespaceFolder(string $namespace, string $entity, ConsoleIo $io)
	{
		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		if (!file_exists($namespaceFolder)) {
			$io->out(sprintf("<success>Created</success> directory %s", $namespaceFolder));
			mkdir($namespaceFolder, 0777, true);
		}
		if (!file_exists($namespaceFolder . DS . "States")) {
			$io->out(sprintf("<success>Created</success> directory %s", $namespaceFolder . DS . "States"));
			mkdir($namespaceFolder . DS . "States", 0777, true);
		}

		return $namespaceFolder;
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




	private function _createStateFiles(string $namespace, string $entity, array $states, ConsoleIo $io): void
	{
		foreach ($states as $state) {
			$this->_createStateFile($namespace, $entity, $state, $io);
		}
	}

	private function _createStateFile(string $namespace, string $entity, string $stateName, ConsoleIo $io): void
	{
		$io->out(sprintf("\nBaking state class for %s...\n", $stateName));

		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		$path = $namespaceFolder . DS . "States" . DS . $stateName . ".php";

		if (file_exists($path)) {
			$io->warning(sprintf("File %s already exists", $path));
			if (!$io->askChoice("Do you want to overwrite it?", ['y', 'n'], 'n')) {
				return;
			}
		}

		$ns = substr($namespace, 1);
		$template = file_get_contents(ROOT . DS . "plugins/FriendsOfBabba/Core/src/Workflow/Template/State.template.php");
		$template = str_replace("namespace FriendsOfBabba\Core\Workflow\Template\States;", "namespace $ns\\$entity\\States;", $template);
		$template = str_replace("class StateTemplate", "class $stateName", $template);
		$template = str_replace("// __STATE_LABEL__", Inflector::humanize($stateName), $template);
		$template = str_replace("// __STATE_NAME__", $stateName, $template);
		$template = str_replace("// __STATE_CODE__", Inflector::dasherize($stateName), $template);

		file_put_contents($path, $template);
		$io->out(sprintf("<success>Wrote</success> `%s`", $path));
	}


	private function _createWorkflowFile(string $namespace, string $entity, array $states, array $routes, ConsoleIo $io)
	{
		$io->info(sprintf("\nBaking workflow class for %s...\n", $entity));

		$namespaceFolder = $this->_getNamespaceFolder($namespace, $entity);
		$path = $namespaceFolder . DS . $entity . ".php";

		if (file_exists($path)) {
			$io->warning(sprintf("File %s already exists", $path));
			if (!$io->askChoice("Do you want to overwrite it?", ['y', 'n'], 'n')) {
				return;
			}
		}

		$ns = substr($namespace, 1);
		$templatePaths = [
			ROOT . DS . "plugins/FriendsOfBabba/Core/src/Workflow/Template/Workflow.template.php",
			ROOT . DS . "vendor/friendsofbabba/core/src/Workflow/Template/Workflow.template.php",
		];
		$template = "";
		foreach ($templatePaths as $templatePath) {
			if (file_exists($templatePath)) {
				$io->out(sprintf("<success>Found</success> Template file `%s` ", $templatePath));
				$template = file_get_contents($templatePath);
				break;
			}
			$io->warning(sprintf("Template file `%s` not exists.", $templatePath));
		}

		if (empty($template)) {
			$io->warning("No template file found. Filter collection will not be created.");
			return;
		}
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

		file_put_contents($path, $template);
	}

	private function _getNamespaceFolder(string $namespace, string $entity): string
	{
		$path = explode('\\', str_replace('\\App\\', '', $namespace));
		$path = implode("/", $path);
		$path = APP . $path . DS . $entity;
		return $path;
	}

	private function _removeDirectory($target)
	{
		if (is_dir($target)) {
			$files = glob($target . '*', GLOB_MARK);
			foreach ($files as $file) {
				$this->_removeDirectory($file);
			}
			rmdir($target);
		} elseif (is_file($target)) {
			unlink($target);
		}
	}
}
