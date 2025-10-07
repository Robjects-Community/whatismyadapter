<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsReliabilityFixture
 */
class ProductsReliabilityFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products_reliability';

    /**
     * Table schema
     *
     * @var array
     */
    public array $fields = [
        'id' => [
            'type' => 'uuid',
            'null' => false,
        ],
        'model' => [
            'type' => 'string',
            'length' => 20,
            'null' => false,
        ],
        'foreign_key' => [
            'type' => 'uuid',
            'null' => false,
        ],
        'total_score' => [
            'type' => 'decimal',
            'length' => 3,
            'precision' => 2,
            'default' => '0.00',
            'null' => false,
        ],
        'completeness_percent' => [
            'type' => 'decimal',
            'length' => 5,
            'precision' => 2,
            'default' => '0.00',
            'null' => false,
        ],
        'field_scores_json' => [
            'type' => 'text',
            'null' => true,
        ],
        'scoring_version' => [
            'type' => 'string',
            'length' => 32,
            'default' => 'v1',
            'null' => false,
        ],
        'last_source' => [
            'type' => 'string',
            'length' => 20,
            'default' => 'system',
            'null' => false,
        ],
        'last_calculated' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'updated_by_user_id' => [
            'type' => 'uuid',
            'null' => true,
        ],
        'updated_by_service' => [
            'type' => 'string',
            'length' => 100,
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
            'idx_rel_model_fk' => [
                'type' => 'unique',
                'columns' => ['model', 'foreign_key'],
            ],
            'idx_rel_total_score' => [
                'type' => 'index',
                'columns' => ['total_score'],
            ],
            'idx_rel_model' => [
                'type' => 'index',
                'columns' => ['model'],
            ],
            'idx_rel_fk' => [
                'type' => 'index',
                'columns' => ['foreign_key'],
            ],
            'idx_rel_last_calculated' => [
                'type' => 'index',
                'columns' => ['last_calculated'],
            ],
            'idx_rel_updated_by_user' => [
                'type' => 'index',
                'columns' => ['updated_by_user_id'],
            ],
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
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
                'id' => '3625d60c-de44-45d8-a648-fa7588bbd654',
                'model' => 'Products',
                'foreign_key' => '09c72674-78fe-4561-83a0-52d726a86a7c',
                'total_score' => '0.85',
                'completeness_percent' => '85.50',
                'field_scores_json' => '{"title":0.95,"description":0.80,"manufacturer":0.75}',
                'scoring_version' => 'v1',
                'last_source' => 'system',
                'last_calculated' => '2025-10-07 15:13:23',
                'updated_by_user_id' => null,
                'updated_by_service' => 'migration:backfill',
                'created' => '2025-10-07 15:13:23',
                'modified' => '2025-10-07 15:13:23',
            ],
        ];
        parent::init();
    }
}
