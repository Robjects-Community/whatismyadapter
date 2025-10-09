<?php
return [
    'users' => [
        'table' => 'users',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'username' => ['type' => 'string', 'length' => 50, 'null' => false],
            'password' => ['type' => 'string', 'length' => 255, 'null' => true],
            'email' => ['type' => 'string', 'length' => 100, 'null' => true],
            'role' => ['type' => 'string', 'length' => 32, 'null' => true],
            'is_admin' => ['type' => 'boolean', 'default' => 0, 'null' => false],
            'active' => ['type' => 'boolean', 'default' => 1, 'null' => false],
            'image' => ['type' => 'string', 'length' => 255, 'null' => true],
            'keywords' => ['type' => 'string', 'length' => 255, 'null' => true],
            'alt_text' => ['type' => 'string', 'length' => 255, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'username_idx' => ['type' => 'index', 'columns' => ['username']],
            'email_idx' => ['type' => 'index', 'columns' => ['email']],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
];
