<?php
/**
 * Quiz System Configuration
 * 
 * This file contains the default configuration for the AI-powered quiz system.
 * These values can be overridden by database settings at runtime.
 */

return [
    'Quiz' => [
        // Global Quiz Settings
        'enabled' => true,
        'max_results' => 10,
        'confidence_threshold' => 0.6,
        
        // Akinator-style Quiz
        'akinator' => [
            'enabled' => true,
            'max_questions' => 15,
            'confidence_goal' => 0.85,
            'binary_threshold' => 0.7,
            'difficulty_level' => 'normal', // easy|normal|hard
            'allow_unsure' => true,
            'dynamic_questions' => true,
        ],
        
        // Comprehensive Quiz
        'comprehensive' => [
            'enabled' => true,
            'steps' => 6,
            'require_all_steps' => false,
            'validate_on_submit' => true,
            'question_types' => ['multiple_choice', 'checkbox', 'range', 'text'],
            'allow_skip' => true,
            'progress_saving' => true,
            'time_limit' => 0, // seconds, 0 = no limit
        ],
        
        // Result Display Options
        'result' => [
            'display' => [
                'show_confidence' => true,
                'show_specs' => true,
                'show_explanation' => true,
                'show_similar' => true,
                'show_alternatives' => true,
                'layout' => 'list', // list|grid|cards
                'max_per_page' => 10,
                'show_images' => true,
                'show_prices' => true,
                'show_ratings' => true,
            ],
            'sorting' => [
                'primary' => 'confidence', // confidence|price|rating|name
                'direction' => 'desc', // asc|desc
                'secondary' => 'rating',
                'allow_user_sort' => true,
            ],
            'filters' => [
                'enable_price_filter' => true,
                'enable_brand_filter' => true,
                'enable_rating_filter' => true,
                'minimum_confidence' => 0.3,
            ],
        ],
        
        // AI Matching Configuration
        'ai' => [
            'method' => 'hybrid', // hybrid|bm25|semantic|rule_based
            'providers' => [
                'semantic' => [
                    'provider' => 'openai', // openai|local|huggingface
                    'model' => 'text-embedding-ada-002',
                    'temperature' => 0.1,
                    'max_tokens' => 1000,
                ],
                'chat' => [
                    'provider' => 'openai',
                    'model' => 'gpt-3.5-turbo',
                    'temperature' => 0.3,
                    'max_tokens' => 500,
                ],
            ],
            'scoring' => [
                'weights' => [
                    'semantic' => 0.4,
                    'keyword' => 0.3,
                    'category' => 0.2,
                    'specs' => 0.1,
                ],
                'boost' => [
                    'verified' => 1.2,
                    'popular' => 1.1,
                    'recent' => 1.05,
                ],
            ],
        ],
        
        // Analytics & Logging
        'analytics' => [
            'enabled' => true,
            'track_submissions' => true,
            'track_abandonment' => true,
            'track_time' => true,
            'track_user_path' => true,
            'track_errors' => true,
            'session_timeout' => 3600, // 1 hour
            'retention_days' => 90,
            'privacy' => [
                'anonymize_ip' => true,
                'store_user_agent' => false,
                'track_personal_data' => false,
            ],
            'reports' => [
                'quiz_completion_rate' => true,
                'popular_paths' => true,
                'common_dropoff_points' => true,
                'result_accuracy' => true,
            ],
        ],
        
        // Caching
        'cache' => [
            'enabled' => true,
            'duration' => 1800, // 30 minutes
            'key_prefix' => 'quiz_',
            'adapters' => [
                'redis' => true,
                'file' => true,
                'memory' => false,
            ],
        ],
        
        // Security Settings
        'security' => [
            'rate_limiting' => [
                'enabled' => true,
                'max_attempts' => 5,
                'window_minutes' => 15,
                'block_duration' => 60, // minutes
            ],
            'csrf_protection' => true,
            'input_sanitization' => true,
            'max_input_length' => 1000,
            'allowed_file_types' => ['jpg', 'jpeg', 'png'],
            'max_file_size' => 5242880, // 5MB
        ],
        
        // Validation Rules
        'validation' => [
            'strict_mode' => false,
            'required_fields' => ['device_type', 'manufacturer'],
            'min_questions_answered' => 2,
            'custom_rules' => [],
        ],
        
        // User Experience Settings
        'ux' => [
            'auto_save' => [
                'enabled' => true,
                'interval' => 30, // seconds
            ],
            'progress_indicator' => [
                'show' => true,
                'type' => 'bar', // bar|steps|percentage
            ],
            'navigation' => [
                'show_back_button' => true,
                'show_skip_button' => true,
                'confirm_before_exit' => true,
            ],
            'accessibility' => [
                'screen_reader' => true,
                'keyboard_navigation' => true,
                'high_contrast' => false,
            ],
            'mobile_optimization' => [
                'responsive_design' => true,
                'touch_friendly' => true,
                'swipe_navigation' => true,
            ],
        ],
        
        // Debug & Development
        'debug' => [
            'enabled' => false,
            'log_level' => 'error', // debug|info|warning|error
            'show_scoring_details' => false,
            'show_ai_reasoning' => false,
            'performance_monitoring' => false,
            'test_mode' => [
                'enabled' => false,
                'mock_ai_responses' => false,
                'force_specific_results' => [],
            ],
        ],
        
        // Legacy configuration for existing quiz questions
        'default' => [
            'version' => 2,
            'quiz_info' => [
                'title' => 'Smart Adapter & Charger Finder Quiz',
                'description' => 'Find the perfect adapter, charger, or cord for your device',
                'estimated_time' => '2-3 minutes'
            ],
            'questions' => [
                [
                    'id' => 'device_type',
                    'type' => 'multiple_choice',
                    'text' => 'What type of device do you need an adapter/charger for?',
                    'required' => true,
                    'weight' => 10,
                    'options' => [
                        ['key' => 'laptop', 'label' => 'Laptop/MacBook'],
                        ['key' => 'phone', 'label' => 'Phone/Mobile Device'],
                        ['key' => 'tablet', 'label' => 'Tablet/iPad'],
                        ['key' => 'gaming', 'label' => 'Gaming Console/Graphics Card'],
                        ['key' => 'other', 'label' => 'Other Electronic Device']
                    ]
                ],
                [
                    'id' => 'manufacturer',
                    'type' => 'multiple_choice', 
                    'text' => 'What is the manufacturer of your device?',
                    'required' => true,
                    'weight' => 9,
                    'options' => [
                        ['key' => 'apple', 'label' => 'Apple (MacBook/iPhone/iPad)'],
                        ['key' => 'dell', 'label' => 'Dell'],
                        ['key' => 'hp', 'label' => 'HP'],
                        ['key' => 'lenovo', 'label' => 'Lenovo'],
                        ['key' => 'asus', 'label' => 'ASUS'],
                        ['key' => 'samsung', 'label' => 'Samsung'],
                        ['key' => 'google', 'label' => 'Google'],
                        ['key' => 'microsoft', 'label' => 'Microsoft'],
                        ['key' => 'other', 'label' => 'Other/Generic']
                    ]
                ],
                [
                    'id' => 'port_type',
                    'type' => 'multiple_choice',
                    'text' => 'What type of charging/connection port does your device have?',
                    'required' => true,
                    'weight' => 8,
                    'options' => [
                        ['key' => 'usb-c', 'label' => 'USB-C'],
                        ['key' => 'lightning', 'label' => 'Lightning (iPhone/iPad)'],
                        ['key' => 'micro-usb', 'label' => 'Micro USB'],
                        ['key' => 'magsafe', 'label' => 'MagSafe (MacBook)'],
                        ['key' => 'proprietary', 'label' => 'Proprietary/Custom Port'],
                        ['key' => 'unsure', 'label' => 'I\'m not sure']
                    ]
                ],
                [
                    'id' => 'power_requirements',
                    'type' => 'multiple_choice',
                    'text' => 'What are your device\'s power requirements?',
                    'required' => false,
                    'weight' => 7,
                    'help_text' => 'Check your device specifications or existing charger for wattage',
                    'options' => [
                        ['key' => '5-18w', 'label' => 'Low Power (5W-18W) - Phones, small devices'],
                        ['key' => '20-65w', 'label' => 'Medium Power (20W-65W) - Tablets, ultrabooks'],
                        ['key' => '70w+', 'label' => 'High Power (70W+) - Gaming laptops, workstations'],
                        ['key' => 'unknown', 'label' => 'I don\'t know']
                    ]
                ],
                [
                    'id' => 'features',
                    'type' => 'multiple_choice',
                    'text' => 'What additional features are important to you?',
                    'required' => false,
                    'weight' => 6,
                    'multiple' => true,
                    'help_text' => 'Select all that apply',
                    'options' => [
                        ['key' => 'fast_charging', 'label' => 'Fast Charging Support'],
                        ['key' => 'multiple_ports', 'label' => 'Multiple Charging Ports'],
                        ['key' => 'wireless', 'label' => 'Wireless Charging'],
                        ['key' => 'portable', 'label' => 'Compact/Portable Design'],
                        ['key' => 'certified', 'label' => 'Official Certification (MFi, etc.)']
                    ]
                ],
                [
                    'id' => 'budget',
                    'type' => 'multiple_choice',
                    'text' => 'What is your budget range?',
                    'required' => true,
                    'weight' => 5,
                    'options' => [
                        ['key' => '5-20', 'label' => '$5 - $20 (Budget)'],
                        ['key' => '20-50', 'label' => '$20 - $50 (Standard)'],
                        ['key' => '50-100', 'label' => '$50 - $100 (Premium)'],
                        ['key' => '100+', 'label' => '$100+ (Professional/High-end)']
                    ]
                ],
                [
                    'id' => 'urgency',
                    'type' => 'multiple_choice',
                    'text' => 'How soon do you need this item?',
                    'required' => false,
                    'weight' => 4,
                    'options' => [
                        ['key' => 'urgent', 'label' => 'ASAP (Same/Next day)'],
                        ['key' => 'normal', 'label' => 'Within a week'],
                        ['key' => 'flexible', 'label' => 'I\'m flexible with timing']
                    ]
                ],
                [
                    'id' => 'priorities',
                    'type' => 'multiple_choice',
                    'text' => 'What is most important to you when choosing an adapter?',
                    'required' => false,
                    'weight' => 3,
                    'options' => [
                        ['key' => 'price', 'label' => 'Low Price'],
                        ['key' => 'quality', 'label' => 'Build Quality'],
                        ['key' => 'brand', 'label' => 'Brand Recognition'],
                        ['key' => 'speed', 'label' => 'Charging Speed'],
                        ['key' => 'durability', 'label' => 'Durability/Longevity']
                    ]
                ]
            ],
            'display' => [
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'show_progress' => true,
                'allow_back' => true
            ],
            'scoring' => [
                'method' => 'weighted_confidence',
                'minimum_match_score' => 0.6,
                'max_results' => 5
            ]
        ]
    ]
];
