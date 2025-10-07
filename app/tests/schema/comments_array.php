<?php
return [
    'comments' => [
        'table' => 'comments',
        'columns' => [
            'id' => ['type' => 'uuid', 'null' => false],
            'foreign_key' => ['type' => 'uuid', 'null' => false],
            'model' => ['type' => 'string', 'length' => 255, 'null' => false],
            'user_id' => ['type' => 'uuid', 'null' => false],
            'content' => ['type' => 'text', 'null' => false],
            'display' => ['type' => 'boolean', 'default' => 1, 'null' => false],
            'is_inappropriate' => ['type' => 'boolean', 'default' => 0, 'null' => false],
            'is_analyzed' => ['type' => 'boolean', 'default' => 0, 'null' => false],
            'inappropriate_reason' => ['type' => 'string', 'length' => 300, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => true],
            'modified' => ['type' => 'datetime', 'null' => true],
            'created_by' => ['type' => 'uuid', 'null' => true],
            'modified_by' => ['type' => 'uuid', 'null' => true],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
