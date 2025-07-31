<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductSpecificationsFixture
 */
class ProductSpecificationsFixture extends TestFixture
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
                'id' => 'spec-001',
                'product_id' => 'prod-001-usb-c-cable',
                'spec_category' => 'Power',
                'spec_name' => 'Power Rating',
                'spec_value' => '27W',
                'spec_unit' => 'watts',
                'spec_type' => 'text',
                'display_order' => 1,
                'is_filterable' => 1,
                'is_searchable' => 1,
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 'spec-002',
                'product_id' => 'prod-001-usb-c-cable',
                'spec_category' => 'Data Transfer',
                'spec_name' => 'Data Speed',
                'spec_value' => '480Mbps',
                'spec_unit' => 'Mbps',
                'spec_type' => 'text',
                'display_order' => 2,
                'is_filterable' => 1,
                'is_searchable' => 1,
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 'spec-003',
                'product_id' => 'prod-001-usb-c-cable',
                'spec_category' => 'Physical',
                'spec_name' => 'Cable Length',
                'spec_value' => '1',
                'spec_unit' => 'meters',
                'spec_type' => 'numeric',
                'display_order' => 3,
                'is_filterable' => 1,
                'is_searchable' => 0,
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 'spec-004',
                'product_id' => 'prod-002-hdmi-adapter',
                'spec_category' => 'Video',
                'spec_name' => 'Maximum Resolution',
                'spec_value' => '4K@60Hz',
                'spec_unit' => null,
                'spec_type' => 'text',
                'display_order' => 1,
                'is_filterable' => 1,
                'is_searchable' => 1,
                'created' => '2024-01-16 09:30:00',
                'modified' => '2024-01-16 09:30:00'
            ],
            [
                'id' => 'spec-005',
                'product_id' => 'prod-002-hdmi-adapter',
                'spec_category' => 'Video',
                'spec_name' => 'HDR Support',
                'spec_value' => '1',
                'spec_unit' => null,
                'spec_type' => 'boolean',
                'display_order' => 2,
                'is_filterable' => 1,
                'is_searchable' => 0,
                'created' => '2024-01-16 09:30:00',
                'modified' => '2024-01-16 09:30:00'
            ],
            [
                'id' => 'spec-006',
                'product_id' => 'prod-003-unpublished',
                'spec_category' => 'General',
                'spec_name' => 'Color',
                'spec_value' => 'Black',
                'spec_unit' => null,
                'spec_type' => 'text',
                'display_order' => 1,
                'is_filterable' => 1,
                'is_searchable' => 1,
                'created' => '2024-01-17 12:00:00',
                'modified' => '2024-01-17 12:00:00'
            ]
        ];
        parent::init();
    }
}
