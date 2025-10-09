<?php
/**
 * Reliability Scoring Configuration
 *
 * This file contains the configuration for the polymorphic reliability
 * scoring system, including field weights, scoring thresholds, and
 * other customizable parameters.
 */

return [
    'Reliability' => [
        /**
         * Products Model Configuration
         */
        'Products' => [
            /**
             * Field weights for reliability scoring.
             * All weights should sum to 1.0 for optimal scoring.
             */
            'fields' => [
                'title' => 0.20,          // Product title (required)
                'description' => 0.20,    // Product description (variable scoring)
                'manufacturer' => 0.15,   // Manufacturer name
                'model_number' => 0.10,   // Model/SKU number
                'price' => 0.15,          // Product price
                'currency' => 0.05,       // Currency code
                'image' => 0.10,          // Product image URL
                'alt_text' => 0.05,       // Image alt text
            ],

            /**
             * Scoring thresholds for variable fields
             */
            'thresholds' => [
                'description' => [
                    'min_length' => 20,       // Minimum length for any score
                    'good_length' => 100,     // Length for 0.75 score
                    'excellent_length' => 300, // Length for 1.0 score
                ],
                'alt_text' => [
                    'min_length' => 5,        // Minimum length for any score
                    'good_length' => 20,      // Length for full score
                ],
                'price' => [
                    'min_value' => 0.01,      // Minimum price to be considered valid
                ],
            ],

            /**
             * Valid currency codes (ISO 4217)
             */
            'valid_currencies' => [
                'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK'
            ],

            /**
             * Image validation settings
             */
            'image_validation' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'require_absolute_url' => false,
                'validate_accessibility' => false, // Future: check if URL is accessible
            ],

            /**
             * Scoring version identifier
             */
            'scoring_version' => 'v1.0',

            /**
             * Whether to normalize total scores to 1.0 maximum
             */
            'normalize_scores' => true,

            /**
             * Completeness calculation method
             * 'binary' - field is either complete (1) or incomplete (0)
             * 'weighted' - use actual field scores for completeness calculation
             */
            'completeness_method' => 'binary',
        ],

        /**
         * Global Settings
         */
        'global' => [
            /**
             * Maximum total score (used for normalization)
             */
            'max_total_score' => 1.00,

            /**
             * Minimum score threshold for auto-approval
             */
            'auto_approve_threshold' => 0.85,

            /**
             * Score thresholds for UI color coding
             */
            'ui_thresholds' => [
                'excellent' => 0.90,  // Green badge
                'good' => 0.70,       // Yellow badge
                'poor' => 0.00,       // Red badge
            ],

            /**
             * Logging configuration
             */
            'logging' => [
                'enabled' => true,
                'log_all_calculations' => true,
                'checksum_verification' => true,
                'retain_logs_days' => 365, // How long to keep reliability logs
            ],

            /**
             * Performance settings
             */
            'performance' => [
                'batch_size' => 100,           // Default batch size for bulk operations
                'cache_scores' => true,        // Whether to cache calculated scores
                'cache_duration' => '1 hour',  // How long to cache scores
            ],
        ],

        /**
         * Future Model Configurations
         * 
         * Uncomment and configure as needed when extending to other models
         */
        /*
        'Articles' => [
            'fields' => [
                'title' => 0.25,
                'body' => 0.35,
                'excerpt' => 0.15,
                'featured_image' => 0.15,
                'meta_description' => 0.10,
            ],
            'scoring_version' => 'v1.0',
        ],

        'Users' => [
            'fields' => [
                'username' => 0.20,
                'email' => 0.20,
                'first_name' => 0.15,
                'last_name' => 0.15,
                'bio' => 0.15,
                'avatar' => 0.15,
            ],
            'scoring_version' => 'v1.0',
        ],
        */
    ]
];
