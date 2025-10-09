<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EmailTemplatesFixture extends TestFixture
{
    public string $table = 'email_templates';

    // Define the table schema for SQLite tests
    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'template_identifier' => ['type' => 'string', 'length' => 50, 'null' => true],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'subject' => ['type' => 'string', 'length' => 255, 'null' => false],
        'body_html' => ['type' => 'text', 'null' => true],
        'body_plain' => ['type' => 'text', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 'e0000000-0000-0000-0000-000000000001',
                'template_identifier' => 'welcome_user',
                'name' => 'Welcome User',
                'subject' => 'Welcome to Willow',
                'body_html' => '<p>Hello {{name}}, welcome!</p>',
                'body_plain' => 'Hello {{name}}, welcome!',
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
        ];
        parent::init();
    }
}
