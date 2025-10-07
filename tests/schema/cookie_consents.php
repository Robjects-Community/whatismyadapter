<?php
return [
    'cookie_consents' => [
        'table' => 'cookie_consents',
        'columns' => [
            'id' => ['type' => 'uuid', 'null' => false],
            'user_id' => ['type' => 'uuid', 'null' => true],
            'ip_address' => ['type' => 'string', 'length' => 45, 'null' => true],
            'consent_type' => ['type' => 'string', 'length' => 50, 'null' => false],
            'consent_given' => ['type' => 'boolean', 'default' => 0, 'null' => false],
            'consent_version' => ['type' => 'string', 'length' => 20, 'null' => true],
            'user_agent' => ['type' => 'string', 'length' => 255, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'indexes' => [
            'user_id_idx' => ['type' => 'index', 'columns' => ['user_id']],
            'consent_type_idx' => ['type' => 'index', 'columns' => ['consent_type']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
