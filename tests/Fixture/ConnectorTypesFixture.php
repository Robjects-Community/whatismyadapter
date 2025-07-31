<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ConnectorTypesFixture
 */
class ConnectorTypesFixture extends TestFixture
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
                'id' => 'conn-001-usb-c',
                'name' => 'usb-c',
                'slug' => 'usb-c',
                'display_name' => 'USB-C',
                'description' => 'Universal Serial Bus Type-C connector',
                'connector_family' => 'USB',
                'is_input' => 1,
                'is_output' => 1,
                'power_capability' => 1,
                'data_capability' => 1,
                'video_capability' => 1,
                'audio_capability' => 1,
                'max_power_watts' => 100.00,
                'max_data_speed_gbps' => 40.000,
                'icon' => 'usb-c-icon',
                'is_active' => 1,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 'conn-002-usb-a',
                'name' => 'usb-a',
                'slug' => 'usb-a',
                'display_name' => 'USB-A',
                'description' => 'Universal Serial Bus Type-A connector',
                'connector_family' => 'USB',
                'is_input' => 1,
                'is_output' => 1,
                'power_capability' => 1,
                'data_capability' => 1,
                'video_capability' => 0,
                'audio_capability' => 0,
                'max_power_watts' => 15.00,
                'max_data_speed_gbps' => 10.000,
                'icon' => 'usb-a-icon',
                'is_active' => 1,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 'conn-003-lightning',
                'name' => 'lightning',
                'slug' => 'lightning',
                'display_name' => 'Lightning',
                'description' => 'Apple Lightning connector',
                'connector_family' => 'Apple',
                'is_input' => 1,
                'is_output' => 1,
                'power_capability' => 1,
                'data_capability' => 1,
                'video_capability' => 0,
                'audio_capability' => 1,
                'max_power_watts' => 27.00,
                'max_data_speed_gbps' => 0.480,
                'icon' => 'lightning-icon',
                'is_active' => 1,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ]
        ];
        parent::init();
    }
}
