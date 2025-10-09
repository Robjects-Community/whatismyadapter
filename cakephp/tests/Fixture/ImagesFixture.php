<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImagesFixture
 */
class ImagesFixture extends TestFixture
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
                'id' => 'f3db978e-d41d-4ade-9e64-ebcaf4322b0c',
                'name' => 'Lorem ipsum dolor sit amet',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'image' => 'test-image.jpg',
                'dir' => 'ImageGalleries/preview',
                'size' => 1,
                'mime' => 'image/jpeg',
                'created' => '2025-07-12 21:48:42',
                'modified' => '2025-07-12 21:48:42',
            ],
            [
                'id' => '1202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Test Image 1',
                'alt_text' => 'Alt 1',
                'keywords' => 'test,one',
                'image' => 'img1.jpg',
                'dir' => 'ImageGalleries/preview',
                'size' => 12345,
                'mime' => 'image/jpeg',
                'created' => '2025-07-12 21:48:42',
                'modified' => '2025-07-12 21:48:42',
            ],
            [
                'id' => '2202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Test Image 2',
                'alt_text' => 'Alt 2',
                'keywords' => 'test,two',
                'image' => 'img2.jpg',
                'dir' => 'ImageGalleries/preview',
                'size' => 23456,
                'mime' => 'image/jpeg',
                'created' => '2025-07-12 21:48:42',
                'modified' => '2025-07-12 21:48:42',
            ],
        ];
        parent::init();
    }
}
