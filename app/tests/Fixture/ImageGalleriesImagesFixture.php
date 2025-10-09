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
                'id' => 'ac79f97d-2924-4085-b636-f8ca5952b944',
                'image_gallery_id' => 'c19da402-ad85-4a4b-b6ab-656874116db6',
                'image_id' => '62703618-80c2-41f5-a809-9cf65ac52719',
                'position' => 1,
                'caption' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-10-07 15:13:00',
                'modified' => '2025-10-07 15:13:00',
            ],
        ];
        parent::init();
    }
}
