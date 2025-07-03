<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImageGalleriesImagesFixture
 */
class ImageGalleriesImagesFixture extends TestFixture
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
                'id' => '011e830d-bdea-4a4a-8529-a9a3511723f3',
                'image_gallery_id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea', // Reference actual gallery from fixture
                'image_id' => '1202d58b-8fec-4b0c-900b-32246bb64d79', // Reference actual image from fixture
                'position' => 1,
                'caption' => 'First test image in gallery',
                'created' => '2025-06-04 08:43:01',
                'modified' => '2025-06-04 08:43:01',
            ],
            [
                'id' => '022e830d-bdea-4a4a-8529-a9a3511723f4',
                'image_gallery_id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea', // Same gallery
                'image_id' => '2202d58b-8fec-4b0c-900b-32246bb64d79', // Second image
                'position' => 2,
                'caption' => 'Second test image in gallery',
                'created' => '2025-06-04 08:43:01',
                'modified' => '2025-06-04 08:43:01',
            ],
        ];
        parent::init();
    }
}
