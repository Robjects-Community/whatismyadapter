<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CableCapabilitiesFixture
 */
class CableCapabilitiesFixture extends TestFixture
{
    public string $table = 'products';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'capability_name' => ['type' => 'string', 'length' => 100, 'null' => true],
        'capability_category' => ['type' => 'string', 'length' => 50, 'null' => true],
        'technical_specifications' => ['type' => 'text', 'null' => true],
        'testing_standard' => ['type' => 'string', 'length' => 255, 'null' => true],
        'certifying_organization' => ['type' => 'string', 'length' => 100, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique_capability' => ['type' => 'unique', 'columns' => ['capability_name']],
        ],
        '_indexes' => [
            'category_idx' => ['type' => 'index', 'columns' => ['capability_category']],
        ],
    ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '5fa96b08-9138-4401-9f4a-df95c77f7647',
                'capability_name' => 'USB 3.0 Data Transfer',
                'capability_category' => 'Data Transfer',
                'technical_specifications' => '{"speed": "5 Gbps", "protocol": "USB 3.0"}',
                'testing_standard' => 'USB-IF Compliance',
                'certifying_organization' => 'USB Implementers Forum',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
