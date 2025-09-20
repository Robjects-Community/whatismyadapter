<?php
/**
 * Routes configuration for AI Adapter Quiz System
 * Add these routes to your config/routes.php file
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use  `$routes` to add routes.
 *
 * The routes for the AI Adapter Quiz system should be added alongside
 * your existing application routes.
 */

// Quiz web interface routes
$routes->scope('/', function (RouteBuilder $routes) {
    // Quiz main pages
    $routes->connect('/quiz', ['controller' => 'Quiz', 'action' => 'index']);
    $routes->connect('/quiz/akinator', ['controller' => 'Quiz', 'action' => 'akinator']);
    $routes->connect('/quiz/comprehensive', ['controller' => 'Quiz', 'action' => 'comprehensive']);
    $routes->connect('/quiz/results', ['controller' => 'Quiz', 'action' => 'results']);

    // AJAX endpoints for web interface
    $routes->connect('/quiz/next-question', ['controller' => 'Quiz', 'action' => 'nextQuestion']);
});

// API routes with JSON extension support
$routes->scope('/api', function (RouteBuilder $routes) {
    // Enable JSON extension
    $routes->setExtensions(['json']);

    // Quiz API endpoints
    $routes->scope('/quiz', function (RouteBuilder $routes) {

        // Start quiz session
        $routes->get('/start/{type}', [
            'controller' => 'Api\Quiz', 
            'action' => 'start'
        ])->setPass(['type']);

        // Get next question
        $routes->post('/next-question', [
            'controller' => 'Api\Quiz', 
            'action' => 'nextQuestion'
        ]);

        // Submit quiz and get results  
        $routes->post('/submit', [
            'controller' => 'Api\Quiz', 
            'action' => 'submit'
        ]);
    });

    // Products API endpoints
    $routes->scope('/products', function (RouteBuilder $routes) {

        // Search products
        $routes->get('/search', [
            'controller' => 'Api\Quiz', 
            'action' => 'search'
        ]);

        // Get product details
        $routes->get('/{id}', [
            'controller' => 'Api\Quiz', 
            'action' => 'product'
        ])->setPass(['id']);

        // RESTful resource routes for products management
        $routes->resources('Products', [
            'only' => ['index', 'view', 'add', 'edit', 'delete']
        ]);
    });
});

// Admin routes (if you have admin functionality)
$routes->prefix('Admin', function (RouteBuilder $routes) {
    $routes->scope('/quiz', function (RouteBuilder $routes) {

        // Admin quiz management
        $routes->connect('/', ['controller' => 'Quiz', 'action' => 'dashboard']);
        $routes->connect('/submissions', ['controller' => 'Quiz', 'action' => 'submissions']);
        $routes->connect('/analytics', ['controller' => 'Quiz', 'action' => 'analytics']);

        // Product management  
        $routes->resources('Products');
        $routes->resources('QuizSubmissions', [
            'only' => ['index', 'view', 'delete']
        ]);
    });
});

// Alternative route patterns for better SEO and user experience
$routes->scope('/', function (RouteBuilder $routes) {

    // Friendly URLs for quiz types
    $routes->connect('/adapter-quiz', [
        'controller' => 'Quiz', 
        'action' => 'index'
    ]);

    $routes->connect('/find-my-adapter', [
        'controller' => 'Quiz', 
        'action' => 'comprehensive'
    ]);

    $routes->connect('/adapter-genie', [
        'controller' => 'Quiz', 
        'action' => 'akinator'
    ]);

    // Product browse pages
    $routes->connect('/adapters', [
        'controller' => 'Products', 
        'action' => 'index'
    ]);

    $routes->connect('/adapters/{manufacturer}', [
        'controller' => 'Products', 
        'action' => 'manufacturer'
    ])->setPass(['manufacturer']);

    $routes->connect('/adapter/{id}', [
        'controller' => 'Products', 
        'action' => 'view'
    ])->setPass(['id']);
});

/* 
 * Route examples for different HTTP methods and API versions:
 */

// Versioned API routes  
$routes->scope('/api/v1', function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    // HTTP method specific routes
    $routes->get('/quiz/questions', ['controller' => 'Api\Quiz', 'action' => 'getQuestions']);
    $routes->post('/quiz/answer', ['controller' => 'Api\Quiz', 'action' => 'submitAnswer']);
    $routes->put('/quiz/update', ['controller' => 'Api\Quiz', 'action' => 'updateQuiz']);
    $routes->delete('/quiz/reset', ['controller' => 'Api\Quiz', 'action' => 'resetQuiz']);

    // Resource routes with custom actions
    $routes->resources('Products', [
        'map' => [
            'recommend' => [
                'action' => 'recommend',
                'method' => 'POST'
            ],
            'compare' => [
                'action' => 'compare', 
                'method' => 'GET'
            ]
        ]
    ]);
});

// Routes with regex constraints
$routes->scope('/api', function (RouteBuilder $routes) {

    // Product ID must be numeric
    $routes->get('/product/{id}', [
        'controller' => 'Api\Quiz', 
        'action' => 'product'
    ])->setPass(['id'])
      ->setPatterns(['id' => '\d+']);

    // Quiz type must be specific values
    $routes->get('/quiz/{type}/start', [
        'controller' => 'Api\Quiz', 
        'action' => 'start'
    ])->setPass(['type'])
      ->setPatterns(['type' => 'comprehensive|akinator']);
});

// Webhook routes for external integrations
$routes->scope('/webhooks', function (RouteBuilder $routes) {
    $routes->post('/quiz-completed', [
        'controller' => 'Webhooks', 
        'action' => 'quizCompleted'
    ]);

    $routes->post('/product-updated', [
        'controller' => 'Webhooks', 
        'action' => 'productUpdated'
    ]);
});

/*
 * CORS and middleware can be applied to specific routes:
 * 
 * $routes->registerMiddleware('cors', new \App\Middleware\CorsMiddleware());
 * $routes->scope('/api', ['middleware' => ['cors']], function (RouteBuilder $routes) {
 *     // API routes that need CORS headers
 * });
 */

/*
 * Rate limiting can be applied to API routes:
 * 
 * $routes->registerMiddleware('rateLimit', new \App\Middleware\RateLimitMiddleware([
 *     'limit' => 100,
 *     'window' => 3600 // 1 hour
 * ]));
 * 
 * $routes->scope('/api', ['middleware' => ['rateLimit']], function (RouteBuilder $routes) {
 *     // Rate limited API routes
 * });
 */
?>