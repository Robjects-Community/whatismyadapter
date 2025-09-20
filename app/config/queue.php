<?php
declare(strict_types=1);

use function Cake\Core\env;

return [
    'Queue' => [
        // Default Redis queue connection - Fast, lightweight jobs
        'default' => [
            'url' => env('QUEUE_DEFAULT_URL', 'redis://willowcms:6379'),
            'queue' => env('QUEUE_DEFAULT_NAME', 'default'),
        ],
        
        // Fast Redis queue - Quick operations (< 30 seconds)
        'fast' => [
            'url' => env('QUEUE_FAST_URL', 'redis://willowcms:6379'),
            'queue' => env('QUEUE_FAST_NAME', 'fast'),
        ],
        
        // Medium priority Redis queue - Standard operations (30s - 5min)
        'medium' => [
            'url' => env('QUEUE_MEDIUM_URL', 'redis://willowcms:6379'),
            'queue' => env('QUEUE_MEDIUM_NAME', 'medium'),
        ],
        
        // RabbitMQ connection for heavy compute jobs - AI/ML operations
        'rabbitmq' => [
            'url' => env('RABBITMQ_URL', 'amqp+bunny://guest:guest@rabbitmq:5672/%2f'),
            'queue' => env('RABBITMQ_QUEUE', 'image_analysis'),
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => (int)env('RABBITMQ_PORT', 5672),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'username' => env('RABBITMQ_USERNAME', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'exchange' => env('RABBITMQ_EXCHANGE', 'app'),
            'routing_key' => env('RABBITMQ_ROUTING_KEY', 'image_analysis'),
            'persistent' => true,
            'ssl' => filter_var(env('RABBITMQ_SSL', 'false'), FILTER_VALIDATE_BOOLEAN),
        ],
        
        // Heavy compute RabbitMQ queue - Long-running operations (> 5min)
        'heavy' => [
            'url' => env('RABBITMQ_HEAVY_URL', 'amqp+bunny://guest:guest@rabbitmq:5672/%2f'),
            'queue' => env('RABBITMQ_HEAVY_QUEUE', 'heavy_compute'),
            'host' => env('RABBITMQ_HOST', 'rabbitmq'),
            'port' => (int)env('RABBITMQ_PORT', 5672),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'username' => env('RABBITMQ_USERNAME', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'exchange' => env('RABBITMQ_EXCHANGE', 'app'),
            'routing_key' => env('RABBITMQ_HEAVY_ROUTING_KEY', 'heavy_compute'),
            'persistent' => true,
            'ssl' => filter_var(env('RABBITMQ_SSL', 'false'), FILTER_VALIDATE_BOOLEAN),
        ],
    ],
];
