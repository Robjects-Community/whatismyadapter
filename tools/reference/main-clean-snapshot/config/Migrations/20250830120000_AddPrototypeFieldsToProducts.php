<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddPrototypeFieldsToProducts extends AbstractMigration
{
    /**
     * Add prototype fields from various tables to products table.
     * This migration adds columns from cable_capabilities, cord_capabilities, 
     * cord_categories, cord_endpoints, cord_physical_specs, device_compatibility,
     * physical_specs, and port_types tables to create a comprehensive prototype
     * schema before normalization.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('products');
        
        // =====================================
        // FIELDS FROM cable_capabilities table
        // =====================================
        $table->addColumn('capability_name', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From cable_capabilities table - main capability name',
            'after' => 'alt_text'
        ]);
        
        $table->addColumn('capability_category', 'string', [
            'null' => true,
            'limit' => 50,
            'comment' => 'From cable_capabilities table - capability grouping',
            'after' => 'capability_name'
        ]);
        
        $table->addColumn('technical_specifications', 'json', [
            'null' => true,
            'comment' => 'From cable_capabilities table - JSON technical specs',
            'after' => 'capability_category'
        ]);
        
        $table->addColumn('testing_standard', 'string', [
            'null' => true,
            'limit' => 255,
            'comment' => 'From cable_capabilities table - testing standard used',
            'after' => 'technical_specifications'
        ]);
        
        $table->addColumn('certifying_organization', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From cable_capabilities table - who certified this',
            'after' => 'testing_standard'
        ]);
        
        // =====================================
        // FIELDS FROM cord_capabilities table
        // =====================================
        $table->addColumn('capability_value', 'string', [
            'null' => true,
            'limit' => 255,
            'comment' => 'From cord_capabilities table - specific capability value',
            'after' => 'certifying_organization'
        ]);
        
        $table->addColumn('numeric_rating', 'decimal', [
            'null' => true,
            'precision' => 10,
            'scale' => 3,
            'comment' => 'From cord_capabilities table - numeric performance rating',
            'after' => 'capability_value'
        ]);
        
        $table->addColumn('is_certified', 'boolean', [
            'null' => false,
            'default' => false,
            'comment' => 'From cord_capabilities table - certification status',
            'after' => 'numeric_rating'
        ]);
        
        $table->addColumn('certification_date', 'date', [
            'null' => true,
            'comment' => 'From cord_capabilities table - when certification was obtained',
            'after' => 'is_certified'
        ]);
        
        // =====================================
        // FIELDS FROM cord_categories table
        // =====================================
        $table->addColumn('parent_category_name', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From cord_categories table - parent category name',
            'after' => 'certification_date'
        ]);
        
        $table->addColumn('category_description', 'text', [
            'null' => true,
            'comment' => 'From cord_categories table - detailed category description',
            'after' => 'parent_category_name'
        ]);
        
        $table->addColumn('category_icon', 'string', [
            'null' => true,
            'limit' => 255,
            'comment' => 'From cord_categories table - icon for category display',
            'after' => 'category_description'
        ]);
        
        $table->addColumn('display_order', 'integer', [
            'null' => false,
            'default' => 0,
            'comment' => 'From cord_categories table - sort order for display',
            'after' => 'category_icon'
        ]);
        
        // =====================================
        // FIELDS FROM cord_endpoints table
        // =====================================
        $table->addColumn('port_type_name', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From cord_endpoints table - type of port/connector',
            'after' => 'display_order'
        ]);
        
        $table->addColumn('endpoint_position', 'string', [
            'null' => true,
            'limit' => 20,
            'comment' => 'From cord_endpoints table - end_a or end_b position',
            'after' => 'port_type_name'
        ]);
        
        $table->addColumn('is_detachable', 'boolean', [
            'null' => false,
            'default' => false,
            'comment' => 'From cord_endpoints table - can the endpoint be detached',
            'after' => 'endpoint_position'
        ]);
        
        $table->addColumn('adapter_functionality', 'text', [
            'null' => true,
            'comment' => 'From cord_endpoints table - what adapter functions are available',
            'after' => 'is_detachable'
        ]);
        
        // =====================================
        // FIELDS FROM cord_physical_specs table
        // =====================================
        $table->addColumn('physical_spec_name', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From cord_physical_specs table - name of physical specification',
            'after' => 'adapter_functionality'
        ]);
        
        $table->addColumn('spec_value', 'string', [
            'null' => true,
            'limit' => 255,
            'comment' => 'From cord_physical_specs table - text value of specification',
            'after' => 'physical_spec_name'
        ]);
        
        $table->addColumn('numeric_value', 'decimal', [
            'null' => true,
            'precision' => 10,
            'scale' => 3,
            'comment' => 'From cord_physical_specs table - numeric value of specification',
            'after' => 'spec_value'
        ]);
        
        // =====================================
        // FIELDS FROM device_compatibility table
        // =====================================
        $table->addColumn('device_category', 'string', [
            'null' => true,
            'limit' => 50,
            'comment' => 'From device_compatibility table - category of compatible device',
            'after' => 'numeric_value'
        ]);
        
        $table->addColumn('device_brand', 'string', [
            'null' => true,
            'limit' => 50,
            'comment' => 'From device_compatibility table - brand of compatible device',
            'after' => 'device_category'
        ]);
        
        $table->addColumn('device_model', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From device_compatibility table - model of compatible device',
            'after' => 'device_brand'
        ]);
        
        $table->addColumn('compatibility_level', 'string', [
            'null' => true,
            'limit' => 20,
            'comment' => 'From device_compatibility table - Full/Partial/Limited/Incompatible',
            'after' => 'device_model'
        ]);
        
        $table->addColumn('compatibility_notes', 'text', [
            'null' => true,
            'comment' => 'From device_compatibility table - notes about compatibility',
            'after' => 'compatibility_level'
        ]);
        
        $table->addColumn('performance_rating', 'decimal', [
            'null' => true,
            'precision' => 3,
            'scale' => 2,
            'comment' => 'From device_compatibility table - performance rating (0.00-9.99)',
            'after' => 'compatibility_notes'
        ]);
        
        $table->addColumn('verification_date', 'date', [
            'null' => true,
            'comment' => 'From device_compatibility table - when compatibility was verified',
            'after' => 'performance_rating'
        ]);
        
        $table->addColumn('verified_by', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From device_compatibility table - who verified compatibility',
            'after' => 'verification_date'
        ]);
        
        $table->addColumn('user_reported_rating', 'decimal', [
            'null' => true,
            'precision' => 3,
            'scale' => 2,
            'comment' => 'From device_compatibility table - user-submitted rating',
            'after' => 'verified_by'
        ]);
        
        // =====================================
        // FIELDS FROM physical_specs table
        // =====================================
        $table->addColumn('spec_type', 'string', [
            'null' => true,
            'limit' => 20,
            'comment' => 'From physical_specs table - measurement/material/rating/boolean/text',
            'after' => 'user_reported_rating'
        ]);
        
        $table->addColumn('measurement_unit', 'string', [
            'null' => true,
            'limit' => 20,
            'comment' => 'From physical_specs table - unit of measurement (mm, cm, kg, etc)',
            'after' => 'spec_type'
        ]);
        
        $table->addColumn('spec_description', 'text', [
            'null' => true,
            'comment' => 'From physical_specs table - detailed description of specification',
            'after' => 'measurement_unit'
        ]);
        
        // =====================================
        // FIELDS FROM port_types table
        // =====================================
        $table->addColumn('port_family', 'string', [
            'null' => true,
            'limit' => 50,
            'comment' => 'From port_types table - USB, HDMI, DisplayPort, etc',
            'after' => 'spec_description'
        ]);
        
        $table->addColumn('form_factor', 'string', [
            'null' => true,
            'limit' => 30,
            'comment' => 'From port_types table - Mini, Micro, Standard, etc',
            'after' => 'port_family'
        ]);
        
        $table->addColumn('connector_gender', 'string', [
            'null' => true,
            'limit' => 15,
            'comment' => 'From port_types table - Male/Female/Reversible',
            'after' => 'form_factor'
        ]);
        
        $table->addColumn('pin_count', 'integer', [
            'null' => true,
            'comment' => 'From port_types table - total number of pins',
            'after' => 'connector_gender'
        ]);
        
        $table->addColumn('max_voltage', 'decimal', [
            'null' => true,
            'precision' => 5,
            'scale' => 2,
            'comment' => 'From port_types table - maximum voltage (V)',
            'after' => 'pin_count'
        ]);
        
        $table->addColumn('max_current', 'decimal', [
            'null' => true,
            'precision' => 5,
            'scale' => 2,
            'comment' => 'From port_types table - maximum current (A)',
            'after' => 'max_voltage'
        ]);
        
        $table->addColumn('data_pin_count', 'integer', [
            'null' => true,
            'comment' => 'From port_types table - number of data pins',
            'after' => 'max_current'
        ]);
        
        $table->addColumn('power_pin_count', 'integer', [
            'null' => true,
            'comment' => 'From port_types table - number of power pins',
            'after' => 'data_pin_count'
        ]);
        
        $table->addColumn('ground_pin_count', 'integer', [
            'null' => true,
            'comment' => 'From port_types table - number of ground pins',
            'after' => 'power_pin_count'
        ]);
        
        $table->addColumn('electrical_shielding', 'string', [
            'null' => true,
            'limit' => 50,
            'comment' => 'From port_types table - type of electrical shielding',
            'after' => 'ground_pin_count'
        ]);
        
        $table->addColumn('durability_cycles', 'integer', [
            'null' => true,
            'comment' => 'From port_types table - expected plug/unplug cycles',
            'after' => 'electrical_shielding'
        ]);
        
        $table->addColumn('introduced_date', 'date', [
            'null' => true,
            'comment' => 'From port_types table - when this connector type was introduced',
            'after' => 'durability_cycles'
        ]);
        
        $table->addColumn('deprecated_date', 'date', [
            'null' => true,
            'comment' => 'From port_types table - when this connector type was deprecated',
            'after' => 'introduced_date'
        ]);
        
        $table->addColumn('physical_specs_summary', 'string', [
            'null' => true,
            'limit' => 100,
            'comment' => 'From port_types table - summary of physical specifications',
            'after' => 'deprecated_date'
        ]);
        
        // =====================================
        // ADDITIONAL PROTOTYPE FIELDS
        // =====================================
        $table->addColumn('prototype_notes', 'text', [
            'null' => true,
            'comment' => 'Prototype development notes and observations',
            'after' => 'physical_specs_summary'
        ]);
        
        $table->addColumn('needs_normalization', 'boolean', [
            'null' => false,
            'default' => true,
            'comment' => 'Flag to indicate this record needs to be normalized into separate tables',
            'after' => 'prototype_notes'
        ]);
        
        // =====================================
        // ADD INDEXES FOR PERFORMANCE
        // =====================================
        $table->addIndex(['capability_name'], ['name' => 'idx_products_capability_name']);
        $table->addIndex(['capability_category'], ['name' => 'idx_products_capability_category']);
        $table->addIndex(['port_family'], ['name' => 'idx_products_port_family']);
        $table->addIndex(['form_factor'], ['name' => 'idx_products_form_factor']);
        $table->addIndex(['connector_gender'], ['name' => 'idx_products_connector_gender']);
        $table->addIndex(['device_category'], ['name' => 'idx_products_device_category']);
        $table->addIndex(['device_brand'], ['name' => 'idx_products_device_brand']);
        $table->addIndex(['compatibility_level'], ['name' => 'idx_products_compatibility_level']);
        $table->addIndex(['is_certified'], ['name' => 'idx_products_is_certified']);
        $table->addIndex(['is_detachable'], ['name' => 'idx_products_is_detachable']);
        $table->addIndex(['needs_normalization'], ['name' => 'idx_products_needs_normalization']);
        $table->addIndex(['performance_rating'], ['name' => 'idx_products_performance_rating']);
        $table->addIndex(['numeric_rating'], ['name' => 'idx_products_numeric_rating']);
        
        // Composite indexes for common search patterns
        $table->addIndex(['port_family', 'form_factor'], ['name' => 'idx_products_port_family_form_factor']);
        $table->addIndex(['device_category', 'device_brand'], ['name' => 'idx_products_device_category_brand']);
        $table->addIndex(['capability_category', 'is_certified'], ['name' => 'idx_products_capability_certified']);
        
        $table->update();
    }
}
