<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
  * So you can use  `$this` to reference the application class instance
  * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);
    $routes->setExtensions(['xml', 'rss']);

    // Root robots.txt route must come before the scope
    $routes->connect(
        '/robots.txt',
        [
            'controller' => 'Robots',
            'action' => 'index'
        ],
        [
            '_name' => 'robots-root'
        ]
    );

    // Root sitemap.xml route must come before the scope
    $routes->connect(
        '/sitemap',
        [
            'controller' => 'Sitemap',
            'action' => 'index',
            '_ext' => 'xml'
        ],
        [
            '_name' => 'sitemap-root'
        ]
    );

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->setExtensions(['xml', 'rss']);
        
        $builder->connect('/', ['controller' => 'Articles', 'action' => 'index']);
        $builder->connect(
            '/',
            [
                'controller' => 'Articles',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'home'
            ]
        );

        // Language-specific robots.txt route
        $builder->connect(
            '/{lang}/robots.txt',
            [
                'controller' => 'Robots',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'robots',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        // Language-specific sitemap route
        $builder->connect(
            '/{lang}/sitemap',
            [
                'controller' => 'Sitemap',
                'action' => 'index',
                '_ext' => 'xml'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'sitemap',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        // Language-specific rss route
        $builder->connect(
            '/{lang}/feed',  // Changed from /rss to /feed
            [
                'controller' => 'Rss',
                'action' => 'index',
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'rss',
                'lang' => '[a-z]{2}',
                'pass' => ['lang']
            ]
        );

        $builder->connect(
            '/users/login',
            [
                'controller' => 'Users',
                'action' => 'login'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'login',
            ]
        );
        $builder->connect('/users/register', ['controller' => 'Users', 'action' => 'register'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        $builder->connect(
            '/users/forgot-password',
            [
                'controller' => 'Users',
                'action' => 'forgot-password'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'forgot-password',
            ]
        );
        $builder->connect(
            '/users/reset-password/{confirmationCode}',
            [
                'controller' => 'Users',
                'action' => 'reset-password'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'reset-password',
                'pass' => ['confirmationCode'],
            ]
        );
        $builder->connect(
            '/users/logout',
            [
                'controller' => 'Users',
                'action' => 'logout'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'logout',
            ]
        );

        $builder->connect(
            '/users/confirm-email/{confirmationCode}',
            [
                'controller' => 'Users',
                'action' => 'confirmEmail'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'confirm-email',
                'pass' => ['confirmationCode'],
            ]
        );

        $builder->connect('/users/edit/{id}',
        [
            'controller' => 'Users',
            'action' => 'edit'
        ],
        [
            '_name' => 'account',
            'routeClass' => 'ADmad/I18n.I18nRoute',
            'pass' => ['id'],
        ]);

        $builder->connect('/articles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        $builder->connect(
            '/tags',
            ['controller' => 'Tags', 'action' => 'index'],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'tags-index',
            ]
        );
        
        $builder->connect(
            'articles/{slug}',
            [
                'controller' => 'Articles',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'article-by-slug',
                'pass' => ['slug'],
            ]
        );

        $builder->connect(
            'pages/{slug}',
            [
                'controller' => 'Articles',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'page-by-slug',
                'pass' => ['slug'] 
            ]
        );

        $builder->connect(
            'tags/{slug}',
            [
                'controller' => 'Tags',
                'action' => 'view-by-slug'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'tag-by-slug',
                'pass' => ['slug'] 
            ]
        );

        $builder->connect(
            'cookie-consents/edit',
            [
                'controller' => 'CookieConsents',
                'action' => 'edit'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'cookie-consent',
            ]
        );
    });

    $routes->prefix('Admin', function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'Articles', 'action' => 'index', 'prefix' => 'Admin']);
        
        // Specific route for removing images from galleries
        $routes->connect(
            '/image-galleries/remove-image/{id}/{imageId}',
            ['controller' => 'ImageGalleries', 'action' => 'removeImage'],
            ['pass' => ['id', 'imageId']]
        );
        
        $routes->fallbacks(DashedRoute::class);
    });

    // Add DebugKit routes with proper context if in debug mode
    if (\Cake\Core\Configure::read('debug')) {
        $routes->plugin('DebugKit', function (RouteBuilder $routes) {
            $routes->fallbacks();
        });
    }
};