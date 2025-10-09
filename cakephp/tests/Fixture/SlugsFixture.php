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
                'id' => 'fd992ca9-870e-4f83-a1cf-40f02e956946',
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'article-one',
                'created' => '2025-07-12 21:48:35',
            ],
            [
                'id' => 'aa992ca9-870e-4f83-a1cf-40f02e956999',
                'model' => 'Tags',
                'foreign_key' => '00000000-0000-0000-0000-000000000001',
                'slug' => 'test-tag',
                'created' => '2025-07-12 21:48:36',
            ],
        ];
        parent::init();
    }
}
