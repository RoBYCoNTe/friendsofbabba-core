<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\IdentifierInterface;
use Authentication\Identity;
use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use FriendsOfBabba\Core\Model\Entity\User;
use FriendsOfBabba\Core\Routing\Middleware\CorsMiddleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Plugin for FriendsOfBabba\Core
 *
 * How to install the plugin:
 *
 *
 */
class Plugin extends BasePlugin implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
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
        $app->addPlugin("Authorization");

        // Configure CORS.
        Configure::load('FriendsOfBabba/Core.default', 'default');

        $defaultConfig = (array) Configure::consume('Cors-default');
        $personnalConfig = (array) Configure::consume('Cors');
        $config = array_merge($defaultConfig, $personnalConfig);

        Configure::write('Cors', $config);
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('user add', \FriendsOfBabba\Core\Command\User\AddCommand::class);
        $commands->add('user pwd', \FriendsOfBabba\Core\Command\User\PwdCommand::class);

        $commands->add('permission scan', \FriendsOfBabba\Core\Command\PermissionCommand::class);
        $commands->add('language', \FriendsOfBabba\Core\Command\LanguageCommand::class);
        $commands->add('install', \FriendsOfBabba\Core\Command\InstallCommand::class);
        $commands->add('migration', \FriendsOfBabba\Core\Command\MigrationCommand::class);

        $commands->add('workflow create', \FriendsOfBabba\Core\Command\Workflow\CreateCommand::class);
        $commands->add('workflow create_transaction_table', \FriendsOfBabba\Core\Command\Workflow\CreateTransactionTableCommand::class);
        $commands->add('workflow create_files', \FriendsOfBabba\Core\Command\Workflow\CreateFilesCommand::class);

        $commands->add('entity create', \FriendsOfBabba\Core\Command\Entity\CreateCommand::class);

        $commands->add('api create', \FriendsOfBabba\Core\Command\Api\CreateCommand::class);

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
        $middlewareQueue->add(new AuthorizationMiddleware($this, [
            'identityDecorator' => function ($auth, $identity) {
                /** @var User */
                $user = $identity->getOriginalData();
                $user->setAuthorization($auth);
                return $user;
            },
            'requireAuthorizationCheck' => false
        ]));
        $middlewareQueue->add(new CorsMiddleware());
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
        $service = new AuthenticationService();
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
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'FriendsOfBabba/Core.Users',
                'finder' => 'authenticated'
            ]
        ]);
        $service->loadIdentifier('Authentication.JwtSubject', [
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'FriendsOfBabba/Core.Users',
                'finder' => 'authenticated'
            ]
        ]);
        $service->loadIdentifier('Authentication.Password', [
            'returnPayload' => false,
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'FriendsOfBabba/Core.Users',
                'finder' => 'authenticated'
            ]
        ]);

        return $service;
    }

    public function getAuthorizationService(ServerRequestInterface $request): AuthorizationServiceInterface
    {
        $resolver = new OrmResolver();
        return new AuthorizationService($resolver);
    }
}
