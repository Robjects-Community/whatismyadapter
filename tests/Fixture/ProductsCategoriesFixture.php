<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProductsCategoriesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'product_id' => 'prod-001-usb-c-cable',
                'category_id' => 'cat-001-charging-cables',
                'is_primary' => 1
            ],
            [
                'product_id' => 'prod-002-hdmi-adapter',
                'category_id' => 'cat-003-video-adapters',
                'is_primary' => 1
            ]
        ];
        parent::init();
    }
}
