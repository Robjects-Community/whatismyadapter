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
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '3625d60c-de44-45d8-a648-fa7588bbd654',
                'model' => 'Lorem ipsum dolor ',
                'foreign_key' => '09c72674-78fe-4561-83a0-52d726a86a7c',
                'total_score' => 1.5,
                'completeness_percent' => 1.5,
                'field_scores_json' => '',
                'scoring_version' => 'Lorem ipsum dolor sit amet',
                'last_source' => 'Lorem ipsum dolor ',
                'last_calculated' => '2025-10-07 15:13:23',
                'updated_by_user_id' => '79766dc3-c20f-4f37-a2eb-879deeea5940',
                'updated_by_service' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:13:23',
                'modified' => '2025-10-07 15:13:23',
            ],
        ];
        parent::init();
    }
}
