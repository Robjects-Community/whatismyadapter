<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsReliabilityFieldsFixture
 */
class ProductsReliabilityFieldsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products_reliability_fields';

    /**
     * Table schema
     *
     * @var array
     */
    public array $fields = [
        'model' => [
            'type' => 'string',
            'length' => 20,
            'null' => false,
        ],
        'foreign_key' => [
            'type' => 'uuid',
            'null' => false,
        ],
        'field' => [
            'type' => 'string',
            'length' => 64,
            'null' => false,
        ],
        'score' => [
            'type' => 'decimal',
            'length' => 3,
            'precision' => 2,
            'default' => '0.00',
            'null' => false,
        ],
        'weight' => [
            'type' => 'decimal',
            'length' => 4,
            'precision' => 3,
            'default' => '0.000',
            'null' => false,
        ],
        'max_score' => [
            'type' => 'decimal',
            'length' => 3,
            'precision' => 2,
            'default' => '1.00',
            'null' => false,
        ],
        'notes' => [
            'type' => 'string',
            'length' => 255,
            'null' => true,
        ],
        'created' => [
            'type' => 'datetime',
            'null' => false,
        ],
        'modified' => [
            'type' => 'datetime',
            'null' => false,
        ],
        '_indexes' => [
            'idx_prf_model_fk' => [
                'type' => 'index',
                'columns' => ['model', 'foreign_key'],
            ],
            'idx_prf_field' => [
                'type' => 'index',
                'columns' => ['field'],
            ],
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['model', 'foreign_key', 'field'],
            ],
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
                'model' => 'Products',
                'foreign_key' => '09c72674-78fe-4561-83a0-52d726a86a7c',
                'field' => 'title',
                'score' => '0.95',
                'weight' => '0.300',
                'max_score' => '1.00',
                'notes' => 'Title is complete and well formatted',
                'created' => '2025-10-07 15:13:17',
                'modified' => '2025-10-07 15:13:17',
            ],
            [
                'model' => 'Products',
                'foreign_key' => '09c72674-78fe-4561-83a0-52d726a86a7c',
                'field' => 'description',
                'score' => '0.80',
                'weight' => '0.250',
                'max_score' => '1.00',
                'notes' => 'Description could be more detailed',
                'created' => '2025-10-07 15:13:17',
                'modified' => '2025-10-07 15:13:17',
            ],
            [
                'model' => 'Products',
                'foreign_key' => '09c72674-78fe-4561-83a0-52d726a86a7c',
                'field' => 'manufacturer',
                'score' => '0.75',
                'weight' => '0.200',
                'max_score' => '1.00',
                'notes' => 'Manufacturer info present',
                'created' => '2025-10-07 15:13:17',
                'modified' => '2025-10-07 15:13:17',
            ],
        ];
        parent::init();
    }
}
