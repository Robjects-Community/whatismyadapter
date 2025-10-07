<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_cord_capabilities table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('cable_capability_id', 'uuid', [
        'null' => false,
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
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('certification_date', 'date', [
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

    $table->addIndex('idx_cord_capabilities_numeric_range', ['type' => 'index', 'columns' => ['cable_capability_id', 'numeric_rating']]);
    $table->addIndex('idx_cord_capabilities_certified', ['type' => 'index', 'columns' => ['cable_capability_id', 'is_certified']]);
    $table->addConstraint('idx_cord_capabilities_unique', ['type' => 'unique', 'columns' => ['product_id', 'cable_capability_id']]);

    return $table;
};
