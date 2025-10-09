<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('article_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('parent_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('lft', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('rght', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('kind', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('title', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 191,
        'null' => false,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('manufacturer', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('model_number', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('price', 'decimal', [
        'length' => 10,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('currency', 'string', [
        'length' => 3,
        'null' => true,
        'default' => 'USD',
        'fixed' => true,  // Indicates fixed-length string (CHAR equivalent)
    ]);
    $table->addColumn('image', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('alt_text', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('capability_name', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('capability_category', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('technical_specifications', 'json', [
        'null' => true,
    ]);
    $table->addColumn('testing_standard', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('certifying_organization', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('capability_value', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('numeric_rating', 'decimal', [
        'length' => 10,
        'precision' => 3,
        'null' => true,
    ]);
    $table->addColumn('is_certified', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('certification_date', 'date', [
        'null' => true,
    ]);
    $table->addColumn('parent_category_name', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('category_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('category_icon', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('display_order', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('port_type_name', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('endpoint_position', 'string', [
        'length' => 20,
        'null' => true,
    ]);
    $table->addColumn('is_detachable', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('adapter_functionality', 'text', [
        'null' => true,
    ]);
    $table->addColumn('physical_spec_name', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('spec_value', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('numeric_value', 'decimal', [
        'length' => 10,
        'precision' => 3,
        'null' => true,
    ]);
    $table->addColumn('device_category', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('device_brand', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('device_model', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('compatibility_level', 'string', [
        'length' => 20,
        'null' => true,
    ]);
    $table->addColumn('compatibility_notes', 'text', [
        'null' => true,
    ]);
    $table->addColumn('performance_rating', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('verification_date', 'date', [
        'null' => true,
    ]);
    $table->addColumn('verified_by', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('user_reported_rating', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('spec_type', 'string', [
        'length' => 20,
        'null' => true,
    ]);
    $table->addColumn('measurement_unit', 'string', [
        'length' => 20,
        'null' => true,
    ]);
    $table->addColumn('spec_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('port_family', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('form_factor', 'string', [
        'length' => 30,
        'null' => true,
    ]);
    $table->addColumn('connector_gender', 'string', [
        'length' => 15,
        'null' => true,
    ]);
    $table->addColumn('pin_count', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('max_voltage', 'decimal', [
        'length' => 5,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('max_current', 'decimal', [
        'length' => 5,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('data_pin_count', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('power_pin_count', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('ground_pin_count', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('electrical_shielding', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('durability_cycles', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('introduced_date', 'date', [
        'null' => true,
    ]);
    $table->addColumn('deprecated_date', 'date', [
        'null' => true,
    ]);
    $table->addColumn('physical_specs_summary', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('prototype_notes', 'text', [
        'null' => true,
    ]);
    $table->addColumn('needs_normalization', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('is_published', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('featured', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('verification_status', 'string', [
        'length' => 20,
        'null' => false,
        'default' => 'pending',
    ]);
    $table->addColumn('reliability_score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => true,
        'default' => '0.00',
    ]);
    $table->addColumn('view_count', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('created_by', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('modified_by', 'uuid', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('idx_products_user', ['type' => 'index', 'columns' => ['user_id']]);
    $table->addIndex('idx_products_article', ['type' => 'index', 'columns' => ['article_id']]);
    $table->addIndex('idx_products_published', ['type' => 'index', 'columns' => ['is_published']]);
    $table->addIndex('idx_products_featured', ['type' => 'index', 'columns' => ['featured']]);
    $table->addIndex('idx_products_verification', ['type' => 'index', 'columns' => ['verification_status']]);
    $table->addIndex('idx_products_manufacturer', ['type' => 'index', 'columns' => ['manufacturer']]);
    $table->addIndex('idx_products_reliability', ['type' => 'index', 'columns' => ['reliability_score']]);
    $table->addIndex('idx_products_created', ['type' => 'index', 'columns' => ['created']]);
    $table->addIndex('idx_products_capability_name', ['type' => 'index', 'columns' => ['capability_name']]);
    $table->addIndex('idx_products_capability_category', ['type' => 'index', 'columns' => ['capability_category']]);
    $table->addIndex('idx_products_port_family', ['type' => 'index', 'columns' => ['port_family']]);
    $table->addIndex('idx_products_form_factor', ['type' => 'index', 'columns' => ['form_factor']]);
    $table->addIndex('idx_products_connector_gender', ['type' => 'index', 'columns' => ['connector_gender']]);
    $table->addIndex('idx_products_device_category', ['type' => 'index', 'columns' => ['device_category']]);
    $table->addIndex('idx_products_device_brand', ['type' => 'index', 'columns' => ['device_brand']]);
    $table->addIndex('idx_products_compatibility_level', ['type' => 'index', 'columns' => ['compatibility_level']]);
    $table->addIndex('idx_products_is_certified', ['type' => 'index', 'columns' => ['is_certified']]);
    $table->addIndex('idx_products_is_detachable', ['type' => 'index', 'columns' => ['is_detachable']]);
    $table->addIndex('idx_products_needs_normalization', ['type' => 'index', 'columns' => ['needs_normalization']]);
    $table->addIndex('idx_products_performance_rating', ['type' => 'index', 'columns' => ['performance_rating']]);
    $table->addIndex('idx_products_numeric_rating', ['type' => 'index', 'columns' => ['numeric_rating']]);
    $table->addIndex('idx_products_port_family_form_factor', ['type' => 'index', 'columns' => ['port_family', 'form_factor']]);
    $table->addIndex('idx_products_device_category_brand', ['type' => 'index', 'columns' => ['device_category', 'device_brand']]);
    $table->addIndex('idx_products_capability_certified', ['type' => 'index', 'columns' => ['capability_category', 'is_certified']]);
    $table->addConstraint('idx_products_slug', ['type' => 'unique', 'columns' => ['slug']]);

    return $table;
};
