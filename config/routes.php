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

use Cake\Core\Configure;

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

        $builder->connect(
            '/users/edit/{id}',
            [
                'controller' => 'Users',
                'action' => 'edit'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'user-edit',
                'pass' => ['id'],
            ]
        );
        
        // Quiz routes - AI-powered quiz functionality
        $builder->connect(
            '/quiz',
            [
                'controller' => 'Quiz',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-index',
            ]
        );
        
        $builder->connect(
            '/quiz/akinator',
            [
                'controller' => 'Quiz',
                'action' => 'akinator'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-akinator',
            ]
        );
        
        $builder->connect(
            '/quiz/comprehensive',
            [
                'controller' => 'Quiz',
                'action' => 'comprehensive'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-comprehensive',
            ]
        );
        
        $builder->connect(
            '/quiz/submit',
            [
                'controller' => 'Quiz',
                'action' => 'submit'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-submit',
                '_method' => 'POST'
            ]
        );
        
        $builder->connect(
            '/quiz/result/{session_id}',
            [
                'controller' => 'Quiz',
                'action' => 'result'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-result',
                'pass' => ['session_id']
            ]
        );
        
        // Legacy routes for backward compatibility
        $builder->connect(
            '/quiz/take',
            [
                'controller' => 'Quiz',
                'action' => 'legacyTake'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-take-legacy',
            ]
        );
        
        $builder->connect(
            '/quiz/preview',
            [
                'controller' => 'Quiz',
                'action' => 'legacyPreview'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'quiz-preview-legacy',
            ]
        );
        
        // Products routes
        $builder->connect(
            '/products',
            [
                'controller' => 'Products',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'products-index',
            ]
        );
        
        // Legacy redirect: /products/quiz -> /quiz
        $builder->connect(
            '/products/quiz',
            [
                'controller' => 'Quiz',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'products-quiz-legacy',
            ]
        );
        
        $builder->connect(
            '/products/add',
            [
                'controller' => 'Products',
                'action' => 'add'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'products-add',
            ]
        );
        
        $builder->connect(
            '/products/view/{id}',
            [
                'controller' => 'Products',
                'action' => 'view'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'products-view',
                'pass' => ['id'],
            ]
        );
        
        $builder->connect(
            '/products/{id}',
            [
                'controller' => 'Products',
                'action' => 'view'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'products-view-short',
                'pass' => ['id'],
            ]
        );

        $builder->connect('/articles/add-comment/*', ['controller' => 'Articles', 'action' => 'addComment'], ['routeClass' => 'ADmad/I18n.I18nRoute']);
        
        // Articles index route
        $builder->connect(
            '/articles',
            [
                'controller' => 'Articles',
                'action' => 'index'
            ],
            [
                'routeClass' => 'ADmad/I18n.I18nRoute',
                '_name' => 'articles-index',
            ]
        );
        
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

    // API routes
    $routes->prefix('Api', function (RouteBuilder $routes) {
        // Set JSON extension for API routes
        $routes->setExtensions(['json']);
        
        // Quiz API routes
        $routes->connect('/quiz/akinator/start', [
            'controller' => 'Quiz',
            'action' => 'akinatorStart'
        ], [
            '_method' => 'POST'
        ]);
        
        $routes->connect('/quiz/akinator/next', [
            'controller' => 'Quiz',
            'action' => 'akinatorNext'
        ], [
            '_method' => 'POST'
        ]);
        
        $routes->connect('/quiz/akinator/result', [
            'controller' => 'Quiz',
            'action' => 'akinatorResult'
        ], [
            '_method' => 'GET'
        ]);
        
        $routes->connect('/quiz/comprehensive/submit', [
            'controller' => 'Quiz',
            'action' => 'comprehensiveSubmit'
        ], [
            '_method' => 'POST'
        ]);
        
        // Products API routes
        $routes->connect('/products', [
            'controller' => 'Products',
            'action' => 'index'
        ], [
            '_method' => 'GET'
        ]);
        
        $routes->connect('/products/{id}', [
            'controller' => 'Products',
            'action' => 'view'
        ], [
            '_method' => 'GET',
            'pass' => ['id']
        ]);
        
        // Reliability API routes
        $routes->connect('/reliability/score', [
            'controller' => 'Reliability',
            'action' => 'score'
        ]);
        $routes->connect('/reliability/verify-checksum', [
            'controller' => 'Reliability', 
            'action' => 'verifyChecksum'
        ]);
        $routes->connect('/reliability/field-stats/{model}', [
            'controller' => 'Reliability',
            'action' => 'fieldStats'
        ], [
            'pass' => ['model']
        ]);
        $routes->connect('/reliability/field-stats', [
            'controller' => 'Reliability',
            'action' => 'fieldStats'
        ]);
        
        // AI Form Field Suggestions API
        $routes->connect('/form-ai-suggestions', [
            'controller' => 'AiFormSuggestions',
            'action' => 'index'
        ]);
    });

    $routes->prefix('Admin', function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'Articles', 'action' => 'index', 'prefix' => 'Admin']);


        // AI Metrics routes
        $routes->connect('/ai-metrics/dashboard', [
            'controller' => 'AiMetrics', 
            'action' => 'dashboard'
        ]);
        $routes->connect('/ai-metrics/realtime-data', [
            'controller' => 'AiMetrics', 
            'action' => 'realtimeData'
        ]);

        
        // Specific route for removing images from galleries
        $routes->connect(
            '/image-galleries/remove-image/{id}/{imageId}',
            ['controller' => 'ImageGalleries', 'action' => 'removeImage'],
            ['pass' => ['id', 'imageId']]
        );


        // //// START OF PRODUCTS ROUTES
        // Products routes
        //dashboard for analytics
        $routes->connect('/products/dashboard', [
            'controller' => 'Products',
            'action' => 'dashboard'
        ]);
        // product admin references for all products (verified, unverified, featured, etc.)
        $routes->connect('/products', [
            'controller' => 'Products',
            'action' => 'index'
        ]);
        // product admin references for all products (verified, unverified, featured, etc.) v2
        $routes->connect('/products/v2', [
            'controller' => 'Products',
            'action' => 'index2'
        ]);
        $routes->connect('/products/view2/*', [
            'controller' => 'Products',
            'action' => 'view2'
        ]);
        $routes->connect('/products/edit2/*', [
            'controller' => 'Products',
            'action' => 'edit2'
        ]);
        $routes->connect('/products/add2', [
            'controller' => 'Products',
            'action' => 'add2'
        ]);

        $routes->connect('/products/toggle-featured/*', [
            'controller' => 'Products',
            'action' => 'toggleFeatured'
        ]);
        $routes->connect('/products/verify/*', [
            'controller' => 'Products',
            'action' => 'verify'
        ]);
        $routes->connect('/products/bulk-verify', [
            'controller' => 'Products',
            'action' => 'bulkVerify'
        ]);
        
        // Pending review and moderation routes
        $routes->connect('/products/pending-review', [
            'controller' => 'Products',
            'action' => 'pendingReview'
        ]);
        
        // Single-item moderation routes
        $routes->connect('/products/approve/*', [
            'controller' => 'Products',
            'action' => 'approve'
        ]);
        $routes->connect('/products/reject/*', [
            'controller' => 'Products',
            'action' => 'reject'
        ]);
        
        // Bulk moderation routes
        $routes->connect('/products/bulk-approve', [
            'controller' => 'Products',
            'action' => 'bulkApprove'
        ]);
        $routes->connect('/products/bulk-reject', [
            'controller' => 'Products',
            'action' => 'bulkReject'
        ]);
        
        // New bulk editing routes
        $routes->connect('/products/bulk-edit', [
            'controller' => 'Products',
            'action' => 'bulkEdit'
        ]);
        $routes->connect('/products/bulk-toggle-published', [
            'controller' => 'Products',
            'action' => 'bulkTogglePublished'
        ]);
        $routes->connect('/products/bulk-toggle-featured', [
            'controller' => 'Products',
            'action' => 'bulkToggleFeatured'
        ]);
        $routes->connect('/products/bulk-delete', [
            'controller' => 'Products',
            'action' => 'bulkDelete'
        ]);
        $routes->connect('/products/bulk-update-fields', [
            'controller' => 'Products',
            'action' => 'bulkUpdateFields'
        ]);
        $routes->connect('/products/forms', [
            'controller' => 'Products',
            'action' => 'forms'
        ]);
        $routes->connect('/products/add', [
            'controller' => 'Products',
            'action' => 'add'
        ]);
        $routes->connect('/products/edit/*', [
            'controller' => 'Products',
            'action' => 'edit'
        ]);
        $routes->connect('/products/delete/*', [
            'controller' => 'Products',
            'action' => 'delete'
        ]);
        $routes->connect('/products/view/*', [
            'controller' => 'Products',
            'action' => 'view'
        ]);
        $routes->connect('/products/reorder', [
            'controller' => 'Products',
            'action' => 'reorder'
        ]);

        // Product Categories routes
        $routes->connect('/product-categories/reorder', [
            'controller' => 'ProductCategories',
            'action' => 'reorder'
        ]);
        
        // Product Form Fields routes
        $routes->connect('/product-form-fields', [
            'controller' => 'ProductFormFields',
            'action' => 'index'
        ]);
        $routes->connect('/product-form-fields/add', [
            'controller' => 'ProductFormFields',
            'action' => 'add'
        ]);
        $routes->connect('/product-form-fields/edit/*', [
            'controller' => 'ProductFormFields',
            'action' => 'edit'
        ]);
        $routes->connect('/product-form-fields/view/*', [
            'controller' => 'ProductFormFields',
            'action' => 'view'
        ]);
        $routes->connect('/product-form-fields/delete/*', [
            'controller' => 'ProductFormFields',
            'action' => 'delete'
        ]);
        $routes->connect('/product-form-fields/reorder/*', [
            'controller' => 'ProductFormFields',
            'action' => 'reorder'
        ]);
        $routes->connect('/product-form-fields/toggle-ai/*', [
            'controller' => 'ProductFormFields',
            'action' => 'toggleAi'
        ]);
        $routes->connect('/product-form-fields/test-ai/*', [
            'controller' => 'ProductFormFields',
            'action' => 'testAi'
        ]);
        $routes->connect('/product-form-fields/reset-order', [
            'controller' => 'ProductFormFields',
            'action' => 'resetOrder'
        ]);
        // END OF PRODUCTS ROUTES

        // Reliability Routes
        $routes->connect('/reliability/{model}/{id}', [
            'controller' => 'Reliability',
            'action' => 'view'
        ], [
            'pass' => ['model', 'id']
        ]);
        $routes->connect('/reliability/{model}/{id}/recalc', [
            'controller' => 'Reliability',
            'action' => 'recalc'
        ], [
            'pass' => ['model', 'id']
        ]);
        $routes->connect('/reliability/{model}/{id}/verify-checksums', [
            'controller' => 'Reliability',
            'action' => 'verifyChecksums'
        ], [
            'pass' => ['model', 'id']
        ]);
        
        $routes->fallbacks(DashedRoute::class);
    });

    // Add DebugKit routes with proper context if in debug mode
    if (Configure::read('debug')) {
        $routes->plugin('DebugKit', function (RouteBuilder $routes) {
            $routes->fallbacks();
        });
        Configure::write('DebugKit.safeTld', ['local', 'localhost', 'dev']);
        Configure::write('DebugKit.ignoreAuthorization', true);


    }
};
