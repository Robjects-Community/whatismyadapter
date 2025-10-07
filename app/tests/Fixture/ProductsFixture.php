<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsFixture
 */
class ProductsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products';

    /**
     * Table schema
     *
     * @var array
     */
    public array $fields = [
        'id' => [
                'type' => 'string',
                'length' => 36,
                'null' => false,
            ],
            'user_id' => [
                'type' => 'string',
                'length' => 36,
                'null' => true,
            ],
            'article_id' => [
                'type' => 'string',
                'length' => 36,
                'null' => true,
            ],
            'parent_id' => [
                'type' => 'string',
                'length' => 36,
                'null' => true,
            ],
            'lft' => [
                'type' => 'integer',
                'null' => true,
            ],
            'rght' => [
                'type' => 'integer',
                'null' => true,
            ],
            'kind' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'title' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'slug' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'description' => [
                'type' => 'text',
                'null' => true,
            ],
            'manufacturer' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'model_number' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'price' => [
                'type' => 'decimal',
                'length' => 10,
                'precision' => 2,
                'null' => true,
            ],
            'currency' => [
                'type' => 'string',
                'length' => 3,
                'null' => true,
            ],
            'image' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'alt_text' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'capability_name' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'capability_category' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'technical_specifications' => [
                'type' => 'text',
                'null' => true,
            ],
            'testing_standard' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'certifying_organization' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'capability_value' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'numeric_rating' => [
                'type' => 'decimal',
                'length' => 5,
                'precision' => 2,
                'null' => true,
            ],
            'is_certified' => [
                'type' => 'boolean',
                'null' => true,
            ],
            'certification_date' => [
                'type' => 'date',
                'null' => true,
            ],
            'parent_category_name' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'category_description' => [
                'type' => 'text',
                'null' => true,
            ],
            'category_icon' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'display_order' => [
                'type' => 'integer',
                'null' => true,
            ],
            'port_type_name' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'endpoint_position' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'is_detachable' => [
                'type' => 'boolean',
                'null' => true,
            ],
            'adapter_functionality' => [
                'type' => 'text',
                'null' => true,
            ],
            'physical_spec_name' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'spec_value' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'numeric_value' => [
                'type' => 'decimal',
                'length' => 10,
                'precision' => 2,
                'null' => true,
            ],
            'device_category' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'device_brand' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'device_model' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'compatibility_level' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'compatibility_notes' => [
                'type' => 'text',
                'null' => true,
            ],
            'performance_rating' => [
                'type' => 'decimal',
                'length' => 5,
                'precision' => 2,
                'null' => true,
            ],
            'verification_date' => [
                'type' => 'date',
                'null' => true,
            ],
            'verified_by' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'user_reported_rating' => [
                'type' => 'decimal',
                'length' => 5,
                'precision' => 2,
                'null' => true,
            ],
            'spec_type' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'measurement_unit' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'spec_description' => [
                'type' => 'text',
                'null' => true,
            ],
            'port_family' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'form_factor' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'connector_gender' => [
                'type' => 'string',
                'length' => 20,
                'null' => true,
            ],
            'pin_count' => [
                'type' => 'integer',
                'null' => true,
            ],
            'max_voltage' => [
                'type' => 'decimal',
                'length' => 10,
                'precision' => 2,
                'null' => true,
            ],
            'max_current' => [
                'type' => 'decimal',
                'length' => 10,
                'precision' => 2,
                'null' => true,
            ],
            'data_pin_count' => [
                'type' => 'integer',
                'null' => true,
            ],
            'power_pin_count' => [
                'type' => 'integer',
                'null' => true,
            ],
            'ground_pin_count' => [
                'type' => 'integer',
                'null' => true,
            ],
            'electrical_shielding' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'durability_cycles' => [
                'type' => 'integer',
                'null' => true,
            ],
            'introduced_date' => [
                'type' => 'date',
                'null' => true,
            ],
            'deprecated_date' => [
                'type' => 'date',
                'null' => true,
            ],
            'physical_specs_summary' => [
                'type' => 'string',
                'length' => 255,
                'null' => true,
            ],
            'prototype_notes' => [
                'type' => 'text',
                'null' => true,
            ],
            'needs_normalization' => [
                'type' => 'boolean',
                'null' => true,
            ],
            'is_published' => [
                'type' => 'boolean',
                'null' => true,
            ],
            'featured' => [
                'type' => 'boolean',
                'null' => true,
            ],
            'verification_status' => [
                'type' => 'string',
                'length' => 50,
                'null' => true,
            ],
            'reliability_score' => [
                'type' => 'decimal',
                'length' => 5,
                'precision' => 2,
                'null' => true,
            ],
            'view_count' => [
                'type' => 'integer',
                'null' => false,
                'default' => 0,
            ],
            'created' => [
                'type' => 'datetime',
                'null' => false,
            ],
            'modified' => [
                'type' => 'datetime',
                'null' => false,
            ],
            'created_by' => [
                'type' => 'string',
                'length' => 36,
                'null' => true,
            ],
            'modified_by' => [
                'type' => 'string',
                'length' => 36,
                'null' => true,
            ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ];


    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'f4ffbf46-8708-4e10-9293-2bd8446069b6',
                'user_id' => '43971b6c-1649-41da-9f83-3b9e2ba1a036',
                'article_id' => 'f7ed1d16-fb4a-4d86-86d4-71e5962cf34a',
                'parent_id' => '88b56806-0ccc-4cf0-8f98-b1fd6dbabea5',
                'lft' => 1,
                'rght' => 1,
                'kind' => 'Lorem ipsum dolor sit amet',
                'title' => 'Lorem ipsum dolor sit amet',
                'slug' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'manufacturer' => 'Lorem ipsum dolor sit amet',
                'model_number' => 'Lorem ipsum dolor sit amet',
                'price' => 1.5,
                'currency' => 'USD',
                'image' => 'product.jpg',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'capability_name' => 'Lorem ipsum dolor sit amet',
                'capability_category' => 'Lorem ipsum dolor sit amet',
                'technical_specifications' => '',
                'testing_standard' => 'Lorem ipsum dolor sit amet',
                'certifying_organization' => 'Lorem ipsum dolor sit amet',
                'capability_value' => 'Lorem ipsum dolor sit amet',
                'numeric_rating' => 1.5,
                'is_certified' => 1,
                'certification_date' => '2025-10-07',
                'parent_category_name' => 'Lorem ipsum dolor sit amet',
                'category_description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'category_icon' => 'Lorem ipsum dolor sit amet',
                'display_order' => 1,
                'port_type_name' => 'Lorem ipsum dolor sit amet',
                'endpoint_position' => 'Lorem ipsum dolor ',
                'is_detachable' => 1,
                'adapter_functionality' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'physical_spec_name' => 'Lorem ipsum dolor sit amet',
                'spec_value' => 'Lorem ipsum dolor sit amet',
                'numeric_value' => 1.5,
                'device_category' => 'Lorem ipsum dolor sit amet',
                'device_brand' => 'Lorem ipsum dolor sit amet',
                'device_model' => 'Lorem ipsum dolor sit amet',
                'compatibility_level' => 'Lorem ipsum dolor ',
                'compatibility_notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'performance_rating' => 1.5,
                'verification_date' => '2025-10-07',
                'verified_by' => 'Lorem ipsum dolor sit amet',
                'user_reported_rating' => 1.5,
                'spec_type' => 'Lorem ipsum dolor ',
                'measurement_unit' => 'Lorem ipsum dolor ',
                'spec_description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'port_family' => 'Lorem ipsum dolor sit amet',
                'form_factor' => 'Lorem ipsum dolor sit amet',
                'connector_gender' => 'Lorem ipsum d',
                'pin_count' => 1,
                'max_voltage' => 1.5,
                'max_current' => 1.5,
                'data_pin_count' => 1,
                'power_pin_count' => 1,
                'ground_pin_count' => 1,
                'electrical_shielding' => 'Lorem ipsum dolor sit amet',
                'durability_cycles' => 1,
                'introduced_date' => '2025-10-07',
                'deprecated_date' => '2025-10-07',
                'physical_specs_summary' => 'Lorem ipsum dolor sit amet',
                'prototype_notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'needs_normalization' => 1,
                'is_published' => 1,
                'featured' => 1,
                'verification_status' => 'Lorem ipsum dolor ',
                'reliability_score' => 1.5,
                'view_count' => 1,
                'created' => '2025-10-07 15:13:25',
                'modified' => '2025-10-07 15:13:25',
                'created_by' => 'f58de74b-cb45-4c1e-8f2f-236ab7b4273c',
                'modified_by' => '77c1bf71-fb0e-4da0-8c1c-3e31197a09b8',
            ],
        ];
        parent::init();
    }
}
