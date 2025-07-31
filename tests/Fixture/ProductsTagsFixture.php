<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProductsTagsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'product_id' => 'prod-001-usb-c-cable',
                'tag_id' => 'tag-001-usb-c'
            ],
            [
                'product_id' => 'prod-001-usb-c-cable', 
                'tag_id' => 'tag-002-charging'
            ]
        ];
        parent::init();
    }
}
