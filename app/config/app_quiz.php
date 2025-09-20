<?php
/**
 * Quiz System Configuration
 * 
 * Configuration settings for the AI-powered quiz system
 */

use Cake\Core\Configure;

return [
    'Quiz' => [
        // Global quiz system settings
        'enabled' => env('QUIZ_ENABLED', true),
        'provider' => env('QUIZ_PROVIDER', 'anthropic'),
        'cache_ttl' => env('QUIZ_CACHE_DURATION', 900), // 15 minutes
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => env('QUIZ_RATE_LIMIT_PER_MINUTE', 60),
            'requests_per_hour' => env('QUIZ_RATE_LIMIT_PER_HOUR', 500),
        ],
        
        // AI matching settings
        'ai_enabled' => env('QUIZ_AI_ENABLED', true),
        'confidence_threshold' => (float)env('QUIZ_CONFIDENCE_THRESHOLD', 0.6),
        'max_results' => (int)env('QUIZ_MAX_RESULTS', 10),
        'fallback_enabled' => true,
        
        // Caching configuration
        'cache' => [
            'engine' => 'Redis', // Use Redis if available, falls back to File
            'duration' => 900, // 15 minutes
            'prefix' => 'quiz_',
            'groups' => ['quiz'],
        ],
        
        // Logging configuration
        'logging' => [
            'enabled' => true,
            'channels' => [
                'quiz' => [
                    'engine' => 'File',
                    'file' => 'logs/quiz.log',
                    'levels' => ['info', 'warning', 'error'],
                ],
                'quiz_api' => [
                    'engine' => 'File',
                    'file' => 'logs/quiz_api.log',
                    'levels' => ['info', 'warning', 'error'],
                    'formatter' => 'json',
                ],
            ],
        ],
        
        // Analytics and tracking
        'analytics' => [
            'enabled' => env('QUIZ_ANALYTICS_ENABLED', true),
            'track_submissions' => true,
            'track_partial_completions' => true,
            'track_api_usage' => true,
            'retention_days' => (int)env('QUIZ_ANALYTICS_RETENTION_DAYS', 90),
        ],
        
        // Akinator-style quiz settings
        'akinator' => [
            'enabled' => true,
            'max_questions' => (int)env('QUIZ_AKINATOR_MAX_QUESTIONS', 15),
            'confidence_threshold' => 0.85,
            'min_products_threshold' => 3,
            'tree_path' => null, // Uses default tree if null
            'cache_ttl' => 3600, // 1 hour for decision tree
            'session_timeout' => 1800, // 30 minutes
        ],
        
        // Comprehensive quiz settings
        'comprehensive' => [
            'enabled' => true,
            'steps' => (int)env('QUIZ_COMPREHENSIVE_STEPS', 6),
            'allow_skip' => false,
            'save_partial' => true,
            'timeout' => 3600, // 1 hour
        ],
        
        // Product matching algorithm settings
        'matching' => [
            'algorithm' => 'hybrid', // 'rule_based', 'ai_only', 'hybrid'
            'weights' => [
                'device_compatibility' => 0.30,
                'manufacturer_match' => 0.20,
                'port_compatibility' => 0.25,
                'price_range' => 0.15,
                'certification' => 0.10,
            ],
            'ai_weight' => 0.6, // AI score weight in hybrid mode
            'rule_weight' => 0.4, // Rule-based score weight in hybrid mode
            'candidate_limit' => 50, // Max candidates to consider
        ],
        
        // API settings
        'api' => [
            'enabled' => true,
            'version' => 'v1',
            'rate_limit' => [
                'anonymous' => 100, // requests per hour
                'authenticated' => 500,
                'premium' => 2000,
            ],
            'cache_control' => [
                'max_age' => 300, // 5 minutes
                'must_revalidate' => true,
            ],
            'cors' => [
                'enabled' => false,
                'origins' => ['*'],
                'methods' => ['GET', 'POST', 'OPTIONS'],
                'headers' => ['Content-Type', 'Authorization', 'X-API-Key'],
            ],
        ],
        
        // Security settings
        'security' => [
            'csrf_enabled' => true, // For web forms only
            'api_key_enabled' => false, // Optional API key authentication
            'ip_whitelist' => [], // Empty = allow all
            'session_security' => [
                'regenerate_id' => true,
                'entropy_length' => 32,
                'timeout' => 1800, // 30 minutes
            ],
        ],
        
        // Performance settings
        'performance' => [
            'lazy_loading' => true,
            'async_ai_scoring' => env('QUIZ_ASYNC_AI', false),
            'queue_heavy_operations' => true,
            'optimize_images' => true,
            'compress_responses' => true,
        ],
        
        // Feature flags
        'features' => [
            'multi_language' => true,
            'social_sharing' => true,
            'export_results' => true,
            'offline_mode' => false,
            'progressive_web_app' => false,
            'voice_input' => false,
            'image_upload' => false,
        ],
        
        // Localization
        'i18n' => [
            'default_locale' => 'en_US',
            'supported_locales' => [
                'en_US' => 'English',
                'es' => 'Español',
                'fr' => 'Français', 
                'de' => 'Deutsch',
                'it' => 'Italiano',
                'pt' => 'Português',
                'ja' => '日本語',
                'zh' => '中文',
            ],
            'fallback_locale' => 'en_US',
            'auto_detect' => true,
        ],
        
        // UI/UX settings
        'ui' => [
            'theme' => 'modern',
            'responsive' => true,
            'animations' => true,
            'progress_indicators' => true,
            'keyboard_shortcuts' => true,
            'accessibility' => [
                'high_contrast' => false,
                'screen_reader' => true,
                'keyboard_navigation' => true,
                'aria_labels' => true,
            ],
        ],
        
        // Error handling
        'error_handling' => [
            'show_detailed_errors' => Configure::read('debug'),
            'log_errors' => true,
            'email_errors' => false,
            'error_email' => env('ERROR_EMAIL', ''),
            'retry_attempts' => 3,
            'retry_delay' => 1000, // milliseconds
        ],
        
        // Testing and development
        'testing' => [
            'mock_ai_responses' => env('QUIZ_MOCK_AI', false),
            'debug_mode' => Configure::read('debug'),
            'profiling_enabled' => false,
            'test_data_enabled' => env('QUIZ_TEST_DATA', false),
        ],
        
        // Backup and maintenance
        'maintenance' => [
            'backup_submissions' => true,
            'cleanup_old_sessions' => true,
            'cleanup_interval' => 'daily',
            'maintenance_mode' => false,
            'maintenance_message' => 'Quiz system is temporarily unavailable for maintenance.',
        ],
    ],
];
