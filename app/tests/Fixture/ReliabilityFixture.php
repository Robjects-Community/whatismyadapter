<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ReliabilityFixture
 * 
 * This is an alias for products_reliability table used in tests
 */
class ReliabilityFixture extends TestFixture
{
    public string $table = 'products_reliability';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'product_id' => ['type' => 'uuid', 'null' => true],
        'reliability_score' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
        'verification_status' => ['type' => 'string', 'length' => 50, 'null' => true],
        'checked_at' => ['type' => 'datetime', 'null' => true],
        'checksum' => ['type' => 'string', 'length' => 64, 'null' => true],
        'is_verified' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'notes' => ['type' => 'text', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'product_id_idx' => ['type' => 'index', 'columns' => ['product_id']],
            'verification_status_idx' => ['type' => 'index', 'columns' => ['verification_status']],
            'checked_at_idx' => ['type' => 'index', 'columns' => ['checked_at']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-rel-1111-1111-111111111111',
                'product_id' => '11111111-prod-1111-1111-111111111111',
                'reliability_score' => 4.5,
                'verification_status' => 'verified',
                'checked_at' => '2025-01-01 10:00:00',
                'checksum' => hash('sha256', 'test_product_data_1'),
                'is_verified' => true,
                'notes' => 'Verified by automated system',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
            [
                'id' => '22222222-rel-2222-2222-222222222222',
                'product_id' => '22222222-prod-2222-2222-222222222222',
                'reliability_score' => 3.0,
                'verification_status' => 'pending',
                'checked_at' => null,
                'checksum' => null,
                'is_verified' => false,
                'notes' => 'Awaiting verification',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
