<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ModelsImagesFixture
 */
class ModelsImagesFixture extends TestFixture
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
                'id' => 'b7e49b59-3802-4040-adc8-913f4822c0b3',
                'model' => 'Lorem ipsum dolor sit amet',
                'foreign_key' => '306156d3-a828-4257-a3fb-23eae2719c17',
                'image_id' => 'de81d065-52a2-4a55-b85e-1353711011a2',
                'created' => '2024-10-15 19:56:28',
                'modified' => '2024-10-15 19:56:28',
            ],
        ];
        parent::init();
    }
}
