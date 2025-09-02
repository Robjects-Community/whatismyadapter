<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImageGalleriesFixture
 */
class ImageGalleriesFixture extends TestFixture
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
                'id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea',
                'name' => 'Lorem ipsum dolor sit amet',
                'slug' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'is_published' => 1,
                'created' => '2025-06-04 08:42:56',
                'modified' => '2025-06-04 08:42:56',
                'created_by' => 'ff20b5da-e3df-4b10-97d9-43b18d131074',
                'modified_by' => '5a71afe2-2e46-4c7d-927f-f279a60af996',
            ],
        ];
        parent::init();
    }
}
