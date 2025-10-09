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
                'id' => '2171392b-84a9-42d5-a560-1f3011748cf0',
                'model' => 'Lorem ipsum dolor sit amet',
                'foreign_key' => '80cb4190-0948-4010-aee4-a192cae02842',
                'image_id' => 'fbdbf9cc-3624-4c50-b320-6238c1d0c6e8',
                'created' => '2025-10-07 15:13:09',
                'modified' => '2025-10-07 15:13:09',
            ],
        ];
        parent::init();
    }
}
