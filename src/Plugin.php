<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use FriendsOfBabba\Core\Hook\HookManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Plugin for FriendsOfBabba\Core
 */
class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        // Add authentication plugin necessary to work with core.
        $app->addPlugin("Authentication");
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('user add', \FriendsOfBabba\Core\Command\User\AddCommand::class);
        $commands->add('installdb', \FriendsOfBabba\Core\Command\InstallDbCommand::class);
        $commands->add('permission scan', \FriendsOfBabba\Core\Command\PermissionCommand::class);
        $commands->add('language', \FriendsOfBabba\Core\Command\LanguageCommand::class);
        $commands->add('install', \FriendsOfBabba\Core\Command\InstallCommand::class);
        $commands->add('migration', \FriendsOfBabba\Core\Command\MigrationCommand::class);
        return $commands;
    }

    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Add your middlewares here
        $middlewareQueue->add(new AuthenticationMiddleware($this));
        return $middlewareQueue;
    }

    /**
     * Returns a service provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $hookName = PluginManager::instance()->getHookFQN('Plugin.getAuthenticationService');
        $serviceOverride = HookManager::instance()->fire($hookName, $request);
        if (!is_null($serviceOverride)) {
            if ($serviceOverride instanceof AuthenticationServiceInterface) {
                return $serviceOverride;
            } else {
                throw new \Exception(sprintf(
                    "Invalid AuthenticationService returned by hook %s",
                    $hookName
                ));
            }
        }
        $service = new AuthenticationService();
        // Define where users should be redirected to when they are not authenticated
        $service->setConfig([
            // 'unauthenticatedRedirect' => Router::url([
            //     'prefix' => 'Api',
            //     'plugin' => 'FriendsOfBabba/Core',
            //     'controller' => 'Users',
            //     'action' => 'login',
            // ]),
            // 'queryParam' => 'redirect',
        ]);

        $fields = [
            IdentifierInterface::CREDENTIAL_USERNAME => 'username',
            IdentifierInterface::CREDENTIAL_PASSWORD => 'password'
        ];
        $service->loadAuthenticator('Authentication.Jwt', [
            'secretKey' => file_get_contents(CONFIG . '/jwt.pem'),
            'algorithms' => ['RS256'],
            'returnPayload' => false
        ]);
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => $fields
        ]);
        $service->loadIdentifier('Authentication.JwtSubject', [
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => PluginManager::instance()->getModelFQN('Users')
            ]
        ]);
        $service->loadIdentifier('Authentication.Password', [
            'returnPayload' => false,
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => PluginManager::instance()->getModelFQN('Users')
            ]
        ]);

        return $service;
    }
}
