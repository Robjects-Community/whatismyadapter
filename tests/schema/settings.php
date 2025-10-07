<?php
return [
    'settings' => [
        'table' => 'settings',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'category' => ['type' => 'string', 'length' => 100, 'null' => false],
            'key_name' => ['type' => 'string', 'length' => 100, 'null' => false],
            'value_type' => ['type' => 'string', 'length' => 20, 'null' => false],
            'value' => ['type' => 'text', 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'category_key_idx' => [
                'type' => 'index',
                'columns' => ['category', 'key_name'],
            ],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
