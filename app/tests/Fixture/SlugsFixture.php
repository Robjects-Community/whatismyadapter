<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SlugsFixture
 */
class SlugsFixture extends TestFixture
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
                'id' => '31a8061b-6533-42fb-a8eb-f4ba852eba17',
                'model' => 'Lorem ipsum dolor ',
                'foreign_key' => '1888e2af-4f83-46d6-91de-5133c0e7f2d5',
                'slug' => 'Lorem ipsum dolor sit amet',
                'created' => 1759868016,
            ],
        ];
        parent::init();
    }
}
