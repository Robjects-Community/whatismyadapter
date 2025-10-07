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
                'id' => 'ee284405-5429-4500-b75c-11c04dc3b268',
                'name' => 'Lorem ipsum dolor sit amet',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'image' => '',
                'dir' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
                'mime' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:13:05',
                'modified' => '2025-10-07 15:13:05',
            ],
        ];
        parent::init();
    }
}
