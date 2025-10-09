<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsFixture extends TestFixture
{
    public string $table = 'comments';

    // Define the table schema for SQLite tests
    public array $fields = [
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
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 'c0000000-0000-0000-0000-000000000001',
                'foreign_key' => '10000000-0000-0000-0000-000000000000',
                'model' => 'Articles',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'content' => 'Great article! Learned a lot.',
                'display' => 1,
                'is_inappropriate' => 0,
                'is_analyzed' => 0,
                'inappropriate_reason' => null,
                'created' => '2025-08-12 10:00:00',
                'modified' => '2025-08-12 10:00:00',
                'created_by' => null,
                'modified_by' => null,
            ],
        ];
        parent::init();
    }
}
