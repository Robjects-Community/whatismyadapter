<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_port_types table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('port_name', 'string', [
        'length' => 100,
        'null' => false,
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
        'null' => false,
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
    $table->addColumn('physical_specs', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('idx_port_types_family', ['type' => 'index', 'columns' => ['port_family']]);
    $table->addIndex('idx_port_types_form_factor', ['type' => 'index', 'columns' => ['form_factor']]);
    $table->addIndex('idx_port_types_max_current', ['type' => 'index', 'columns' => ['max_current']]);
    $table->addIndex('idx_port_types_max_voltage', ['type' => 'index', 'columns' => ['max_voltage']]);
    $table->addIndex('idx_port_types_active_ports', ['type' => 'index', 'columns' => ['deprecated_date']]);
    $table->addConstraint('idx_port_types_name', ['type' => 'unique', 'columns' => ['port_name']]);

    return $table;
};
