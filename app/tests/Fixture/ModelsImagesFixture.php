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
                'id' => 'df8853af-5b65-4d5d-92a4-0d329ff71dd8',
                'model' => 'Lorem ipsum dolor sit amet',
                'foreign_key' => '8ac77f13-444e-4616-8a81-0427ea96b873',
                'image_id' => '0381d76c-0890-4552-8a14-2e45b9c7434f',
                'created' => '2025-07-12 21:48:43',
                'modified' => '2025-07-12 21:48:43',
            ],
        ];
        parent::init();
    }
}
