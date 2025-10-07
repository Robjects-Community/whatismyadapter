<?php
return [
    'ai_metrics' => [
        'table' => 'ai_metrics',
        'columns' => [
            'id' => [
                'type' => 'string',
                'length' => 36,
                'null' => false,
            ],
            'task_type' => [
                'type' => 'string',
                'length' => 50,
                'null' => false,
            ],
            'execution_time_ms' => [
                'type' => 'integer',
                'null' => true,
            ],
            'tokens_used' => [
                'type' => 'integer',
                'null' => true,
            ],
            'cost_usd' => [
                'type' => 'decimal',
                'length' => 10,
                'precision' => 6,
                'null' => true,
            ],
            'success' => [
                'type' => 'boolean',
                'default' => true,
                'null' => false,
            ],
            'error_message' => [
                'type' => 'text',
                'null' => true,
            ],
            'model_used' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => false,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => false,
            ],
        ],
        'indexes' => [
            'task_type_idx' => [
                'type' => 'index',
                'columns' => ['task_type'],
            ],
            'created_idx' => [
                'type' => 'index',
                'columns' => ['created'],
            ],
            'success_idx' => [
                'type' => 'index',
                'columns' => ['success'],
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
