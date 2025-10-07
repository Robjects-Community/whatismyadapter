<?php
return [
    'aiprompts' => [
        'table' => 'aiprompts',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'task_type' => ['type' => 'string', 'length' => 50, 'null' => false],
            'system_prompt' => ['type' => 'text', 'null' => false],
            'model' => ['type' => 'string', 'length' => 50, 'null' => false],
            'max_tokens' => ['type' => 'integer', 'null' => false],
            'temperature' => ['type' => 'decimal', 'length' => 4, 'precision' => 2, 'null' => false],
            'status' => ['type' => 'string', 'length' => 50, 'null' => true],
            'last_used' => ['type' => 'datetime', 'null' => true],
            'usage_count' => ['type' => 'integer', 'default' => 0, 'null' => false],
            'success_rate' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'description' => ['type' => 'text', 'null' => true],
            'preview_sample' => ['type' => 'text', 'null' => true],
            'expected_output' => ['type' => 'text', 'null' => true],
            'is_active' => ['type' => 'boolean', 'default' => 1, 'null' => false],
            'category' => ['type' => 'string', 'length' => 100, 'null' => true],
            'version' => ['type' => 'string', 'length' => 50, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'task_type_idx' => ['type' => 'index', 'columns' => ['task_type']],
            'model_idx' => ['type' => 'index', 'columns' => ['model']],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
