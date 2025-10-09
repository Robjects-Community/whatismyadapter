<?php
return [
    'page_views' => [
        'table' => 'page_views',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'path' => ['type' => 'string', 'length' => 255, 'null' => false],
            'user_agent' => ['type' => 'string', 'length' => 255, 'null' => true],
            'ip' => ['type' => 'string', 'length' => 45, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'path_idx' => ['type' => 'index', 'columns' => ['path']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
