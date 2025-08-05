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
                'product_id' => '970fe086-869f-48a6-89ef-fb52293e54af',
                'tag_id' => 'c023c907-47d2-448d-8f02-ab1b59ea977c',
            ],
        ];
        parent::init();
    }
}
