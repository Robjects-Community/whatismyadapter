<?php
return [
    'articles' => [
        'table' => 'articles',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'user_id' => ['type' => 'string', 'length' => 36, 'null' => true],
            'title' => ['type' => 'string', 'length' => 200, 'null' => false],
            'slug' => ['type' => 'string', 'length' => 200, 'null' => true],
            'body' => ['type' => 'text', 'null' => true],
            'status' => ['type' => 'string', 'length' => 20, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'user_idx' => ['type' => 'index', 'columns' => ['user_id']],
            'slug_idx' => ['type' => 'index', 'columns' => ['slug']],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
