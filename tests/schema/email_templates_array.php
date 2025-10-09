<?php
return [
    'email_templates' => [
        'table' => 'email_templates',
        'columns' => [
            'id' => ['type' => 'uuid', 'null' => false],
            'template_identifier' => ['type' => 'string', 'length' => 50, 'null' => true],
            'name' => ['type' => 'string', 'length' => 255, 'null' => false],
            'subject' => ['type' => 'string', 'length' => 255, 'null' => false],
            'body_html' => ['type' => 'text', 'null' => true],
            'body_plain' => ['type' => 'text', 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ],
];
