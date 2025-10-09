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
                'product_id' => 'e297cd37-1b18-40ae-976b-583300daa161',
                'tag_id' => '93264b97-98cb-486d-9cf3-581a829fbf75',
            ],
        ];
        parent::init();
    }
}
