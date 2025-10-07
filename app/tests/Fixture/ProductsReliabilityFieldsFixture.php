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
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'model' => '67ba6626-9378-4e17-9950-f64ac6f74793',
                'foreign_key' => '2a069af1-34dc-4541-b610-283069af317b',
                'field' => '5889b52d-6b5f-4d08-848f-b473dd52b6af',
                'score' => 1.5,
                'weight' => 1.5,
                'max_score' => 1.5,
                'notes' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:13:17',
                'modified' => '2025-10-07 15:13:17',
            ],
        ];
        parent::init();
    }
}
