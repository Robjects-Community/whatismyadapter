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
     * Table name
     *
     * @var string
     */
    public string $table = 'connector_types';

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
                'icon' => null,
                'is_active' => 1,
                'created' => '2024-01-01 10:00:00',
                'modified' => '2024-01-01 10:00:00'
            ]
        ];
        parent::init();
    }
}
