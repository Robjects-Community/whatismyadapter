<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsTagsFixture
 */
class ProductsTagsFixture extends TestFixture
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
                'product_id' => 'f8df4ac3-9e88-4b55-84cb-89b43f315dca',
                'tag_id' => 'e4717488-9d14-4c36-a5c0-d20679b25c58',
            ],
        ];
        parent::init();
    }
}
