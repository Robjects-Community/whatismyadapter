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
                'id' => '1202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Test Gallery Image 1',
                'alt_text' => 'First test image for gallery',
                'keywords' => 'test gallery image',
                'image' => 'test-image-1.png', // Must have actual filename for gallery to work
                'dir' => 'files/Images/image/',
                'size' => 1024,
                'mime' => 'image/png',
                'created' => '2024-10-15 19:58:23',
                'modified' => '2024-10-15 19:58:23',
            ],
            [
                'id' => '2202d58b-8fec-4b0c-900b-32246bb64d79',
                'name' => 'Test Gallery Image 2',
                'alt_text' => 'Second test image for gallery',
                'keywords' => 'test gallery image',
                'image' => 'test-image-2.jpg', // Must have actual filename for gallery to work
                'dir' => 'files/Images/image/',
                'size' => 2048,
                'mime' => 'image/jpeg',
                'created' => '2024-10-15 19:58:23',
                'modified' => '2024-10-15 19:58:23',
            ],
        ];
        parent::init();
    }
}
