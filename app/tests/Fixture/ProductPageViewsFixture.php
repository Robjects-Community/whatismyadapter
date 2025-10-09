<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductPageViewsFixture
 */
class ProductPageViewsFixture extends TestFixture
{
    public string $table = 'product_page_views';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'product_id' => ['type' => 'uuid', 'null' => false],
        'ip_address' => ['type' => 'string', 'length' => 45, 'null' => true],
        'user_agent' => ['type' => 'string', 'length' => 500, 'null' => true],
        'referer' => ['type' => 'string', 'length' => 500, 'null' => true],
        'session_id' => ['type' => 'string', 'length' => 100, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'product_id_idx' => ['type' => 'index', 'columns' => ['product_id']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
            'session_id_idx' => ['type' => 'index', 'columns' => ['session_id']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-view-1111-1111-111111111111',
                'product_id' => '11111111-prod-1111-1111-111111111111',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'referer' => 'https://google.com',
                'session_id' => 'sess_12345',
                'created' => '2025-01-01 10:00:00',
            ],
            [
                'id' => '22222222-view-2222-2222-222222222222',
                'product_id' => '11111111-prod-1111-1111-111111111111',
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'referer' => null,
                'session_id' => 'sess_67890',
                'created' => '2025-01-01 11:00:00',
            ],
        ];
        parent::init();
    }
}
