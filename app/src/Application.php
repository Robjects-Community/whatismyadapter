<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use ADmad\I18n\Middleware\I18nMiddleware;
use App\Middleware\IpBlockerMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Utility\I18nManager;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();
        require CONFIG . 'log_config.php';

        // All plugins are now loaded via config/plugins.php
        // This provides a single source of truth for plugin configuration
        // and handles environment-specific loading automatically

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false),
            );
        }

        I18nManager::setEnabledLanguages();

        // Load more plugins here
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this));

        $middlewareQueue
            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            ->add(new I18nMiddleware([
                // If `true` will attempt to get matching languges in "languages" list based
                // on browser locale and redirect to that when going to site root.
                'detectLanguage' => true,
                // Default language for app. If language detection is disabled or no
                // matching language is found redirect to this language
                'defaultLanguage' => 'en',
                // Languages available in app. The keys should match the language prefix used
                // in URLs. Based on the language the locale will be also set.
                'languages' => [
                    'en' => ['locale' => 'en_GB'],
                ],
            ]))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Add authentication middleware
            ->add(new AuthenticationMiddleware($this))

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]));

            // Only add security middleware if not in test environment
            // or if specifically enabled for testing
        if (env('CAKE_ENV') !== 'test' || Configure::read('TestSecurity.enabled', false)) {
            $middlewareQueue
                ->add(new IpBlockerMiddleware())
                ->add(new RateLimitMiddleware());
        }

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        // $this->addOptionalPlugin('Bake');

        // $this->addPlugin('Migrations');

        // Load more plugins here
    }

    /**
     * Returns an authentication service instance.
     *
     * This method configures and returns an instance of the `AuthenticationService` class,
     * which is responsible for handling authentication in the application.
     *
     * The authentication service is configured with the following settings:
     * - `unauthenticatedRedirect`: The URL to redirect users to if they are not authenticated.
     * - `queryParam`: The query parameter to use for redirecting after successful authentication.
     *
     * The `loadAuthenticator` method is used to add authenticators to the service. The `Session`
     * authenticator is loaded first, and the `Form` authenticator is loaded next with the specified
     * fields and login URL.
     *
     * The `loadIdentifier` method is used to add identifiers to the service. The `Password` identifier
     * is loaded with the specified fields and resolver configuration.
     *
     * The resolver configuration specifies the user model and finder to be used for authentication.
     * The `finder` option is set to `auth` to use a custom finder for retrieving user records.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request object.
     * @return \Authentication\AuthenticationServiceInterface The authentication service instance.
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $authenticationService = new AuthenticationService([
            'unauthenticatedRedirect' => Router::url(['prefix' => false, 'controller' => 'Users', 'action' => 'login']),
            'queryParam' => 'redirect',
        ]);

        $fields = [
                AbstractIdentifier::CREDENTIAL_USERNAME => 'email',
                AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
        ];

        // Load the authenticators, you want session first
        $authenticationService->loadAuthenticator('Authentication.Session');
        // Configure form data check to pick email and password
        $authenticationService->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
            'loginUrl' => Router::url(['prefix' => false, 'controller' => 'Users', 'action' => 'login']),
        ]);

        // Load identifiers, ensure we check email and password fields
        $authenticationService->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
            'resolver' => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
                'finder' => 'auth',
            ],
        ]);

        return $authenticationService;
    }
}
