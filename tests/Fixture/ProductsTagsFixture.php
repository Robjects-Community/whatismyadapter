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
        * This method initializes the fixture with an empty records array.
        * It is used to set up the fixture for testing purposes.
        *
        * @return void
        */
    public function init(): void
    {
        $this->records = [
            [
                'product_id' => '1a2b3c4d-5678-90ab-cdef-1234567890ab',
                'tag_id' => '12345678-90ab-cdef-1234-567890abcdef',
            ],
        ];
        parent::init();
    }
}
