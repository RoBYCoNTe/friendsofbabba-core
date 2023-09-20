<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use FriendsOfBabba\Core\Workflow\WorkflowFactory;
use FriendsOfBabba\Core\Model\CrudFactory;
use Cake\Utility\Inflector;

/** @var RouteBuilder $routes */
$routes->prefix("api", function (RouteBuilder $builder) {
	/**
	 * Autoload workflow entities.
	 */
	$workflows = WorkflowFactory::instance()->getConfigured();
	$names = array_keys($workflows);
	foreach ($names as $workflow) {
		$builder->resources($workflow, ['inflect' => 'dasherize']);
	}

	$tables = CrudFactory::instance()->getListOfTables();
	foreach ($tables as $tableName) {
		$controller = Inflector::camelize($tableName);
		$builder->connect('/{controller}/order', [
			'controller' => $controller,
			'action' => 'order',
			'prefix' => 'api',
			'_method' => 'POST'
		]);
		$builder->connect('/{controller}/delete-all', [
			'controller' => $controller,
			'action' => 'deleteAll',
			'prefix' => 'api',
			'_method' => 'DELETE'
		]);
	}
});

/** @var RouteBuilder $routes */
$routes->plugin(
	'FriendsOfBabba/Core',
	['path' => '/'],
	function (RouteBuilder $routes) {
		$routes->setExtensions(['json']);
		$routes->prefix('api', function (RouteBuilder $builder) {
			$builder->setExtensions(['json']);
			$builder->resources('Users');
			$builder->resources('Roles');
			$builder->resources('Notifications');
			$builder->resources('Languages', ['inflect' => 'dasherize']);
			$builder->resources('LanguageMessages', ['inflect' => 'dasherize']);
			$builder->resources('Resources');
			$builder->resources('Commands');
			$builder->resources('CommandLogs', ['inflect' => 'dasherize']);
			$builder->resources('CommandLogRows', ['inflect' => 'dasherize']);

			$builder->connect("/language-messages/generate/:resource", [
				'controller' => 'LanguageMessages',
				'action' => 'generate',
				'prefix' => 'api',
				'inflect' => 'dasherize'
			], [
				'pass' => ['resource']
			]);

			$builder->connect("/languages/put-message", [
				'controller' => 'Languages',
				'action' => 'putMessage',
				'prefix' => 'api',
				'inflect' => 'dasherize'
			]);

			$builder->connect('/.well-known/*', [
				'controller' => 'Jwks',
				'action' => 'index'
			]);
			$builder->connect('/users/login', [
				'controller' => 'Users',
				'action' => 'login'
			]);
			$builder->connect('/users/impersonate', [
				'controller' => 'Users',
				'action' => 'impersonate'
			]);
			$builder->connect('/users/reset-password', [
				'controller' => 'Users',
				'action' => 'resetPassword'
			]);
			$builder->connect('/users/profile', [
				'controller' => 'Users',
				'action' => 'profile'
			]);
			$builder->connect('/notifications/stats', [
				'controller' => 'Notifications',
				'action' => 'stats'
			]);
			$builder->connect('/languages/load', [
				'controller' => 'Languages',
				'action' => 'load'
			]);

			$builder->connect("/workflow/load", [
				'controller' => 'Workflow',
				'action' => 'load',
				'prefix' => 'api',
				'_method' => 'GET'
			]);
			$builder->connect("/workflow/resolve/:resource", [
				'controller' => 'Workflow',
				'action' => 'resolve',
				'prefix' => 'api',
				'_method' => 'GET'
			], ['pass' => ['resource']]);
			$builder->connect("/workflow/transactions/:resource", [
				'controller' => 'Workflow',
				'action' => 'getTransactions',
				'prefix' => 'api',
			], ['pass' => ['resource']]);


			$builder->connect('/crud/load', [
				'controller' => 'Crud',
				'action' => 'load',
				'prefix' => 'api'
			]);
			$builder->connect('/crud/load/:resource', [
				'controller' => 'Crud',
				'action' => 'load',
				'prefix' => 'api'
			], ['pass' => ['resource']]);

			$builder->connect('/crud/:resource/export.:extension', [
				'controller' => 'Crud',
				'action' => 'export',
				'prefix' => 'api'
			], ['pass' => ['resource', 'extension']]);

			$builder->connect('/spid/:action', [
				'controller' => 'Spid',
				'prefix' => 'api'
			], ['pass' => ['action']]);
			$builder->connect('/spid', [
				'controller' => 'Spid',
				'action' => 'add',
				'prefix' => 'api'
			], [
				'_method' => 'POST'
			]);
		});

		$routes->setRouteClass(DashedRoute::class);
	}
);
