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
        'id' => '6a32d011-7871-463b-9b63-7e1ec650ebaf',
                'image_gallery_id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea',
                'image_id' => 'f3db978e-d41d-4ade-9e64-ebcaf4322b0c',
                'position' => 1,
                'caption' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-07-12 21:48:41',
                'modified' => '2025-07-12 21:48:41',
            ],
            [
                'id' => '7b444f77-1234-4c22-9a21-11d2a34f55aa',
                'image_gallery_id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea',
                'image_id' => 'f3db978e-d41d-4ade-9e64-ebcaf4322b0c',
                'position' => 2,
                'caption' => 'Second image caption',
                'created' => '2025-07-12 21:48:42',
                'modified' => '2025-07-12 21:48:42',
            ],
        ];
        parent::init();
    }
}
