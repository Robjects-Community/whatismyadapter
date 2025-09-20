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
                'product_id' => '11111111-2222-3333-4444-555555555555',
                'tag_id' => '66666666-7777-8888-9999-aaaaaaaaaaaa',
            ],
        ];
        parent::init();
    }
}
