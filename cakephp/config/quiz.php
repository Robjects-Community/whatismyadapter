<?php
/**
 * Quiz Configuration
 * 
 * Default quiz configuration for Adapter Finder Quiz.
 * This provides fallback configuration when database settings are not available.
 */

return [
    'Quiz' => [
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
