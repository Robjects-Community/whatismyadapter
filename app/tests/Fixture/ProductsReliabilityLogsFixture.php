<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsReliabilityLogsFixture
 */
class ProductsReliabilityLogsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products_reliability_logs';

    /**
     * Table schema
     *
     * @var array
     */
    public array $fields = [
        'id' => [
            'type' => 'string',
            'length' => 36,
            'null' => false,
        ],
        'model' => [
            'type' => 'string',
            'length' => 255,
            'null' => false,
        ],
        'foreign_key' => [
            'type' => 'string',
            'length' => 36,
            'null' => false,
        ],
        'from_total_score' => [
            'type' => 'decimal',
            'length' => 5,
            'precision' => 2,
            'null' => true,
        ],
        'to_total_score' => [
            'type' => 'decimal',
            'length' => 5,
            'precision' => 2,
            'null' => true,
        ],
        'from_field_scores_json' => [
            'type' => 'text',
            'null' => true,
        ],
        'to_field_scores_json' => [
            'type' => 'text',
            'null' => true,
        ],
        'source' => [
            'type' => 'string',
            'length' => 255,
            'null' => true,
        ],
        'actor_user_id' => [
            'type' => 'string',
            'length' => 36,
            'null' => true,
        ],
        'actor_service' => [
            'type' => 'string',
            'length' => 255,
            'null' => true,
        ],
        'message' => [
            'type' => 'text',
            'null' => true,
        ],
        'checksum_sha256' => [
            'type' => 'string',
            'length' => 64,
            'null' => true,
        ],
        'created' => [
            'type' => 'datetime',
            'null' => false,
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
                'id' => 'b8ea4053-03db-4b58-92db-e5c86c2d6fc7',
                'model' => 'Lorem ipsum dolor ',
                'foreign_key' => '13e5017b-e012-4af6-a306-519334ba96ed',
                'from_total_score' => 1.5,
                'to_total_score' => 1.5,
                'from_field_scores_json' => '',
                'to_field_scores_json' => '',
                'source' => 'Lorem ipsum dolor ',
                'actor_user_id' => '2b7bad29-c856-4513-b1fa-d1fa7de6c22a',
                'actor_service' => 'Lorem ipsum dolor sit amet',
                'message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'checksum_sha256' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'created' => '2025-10-07 15:13:20',
            ],
        ];
        parent::init();
    }
}
