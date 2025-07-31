<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProductConnectorsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'pc-001',
                'product_id' => 'prod-001-usb-c-cable',
                'connector_type_id' => 'conn-001-usb-c',
                'connector_role' => 'input',
                'connector_position' => 'end-a',
                'quantity' => 1,
                'power_rating_watts' => 27.00,
                'data_speed_gbps' => 0.480,
                'notes' => 'USB-C input connector'
            ],
            [
                'id' => 'pc-002',
                'product_id' => 'prod-001-usb-c-cable',
                'connector_type_id' => 'conn-003-lightning',
                'connector_role' => 'output',
                'connector_position' => 'end-b',
                'quantity' => 1,
                'power_rating_watts' => 27.00,
                'data_speed_gbps' => 0.480,
                'notes' => 'Lightning output connector'
            ]
        ];
        parent::init();
    }
}
