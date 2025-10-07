<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_cord_physical_specs table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('physical_spec_id', 'uuid', [
        'null' => false,
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

    $table->addIndex('idx_cord_physical_specs_numeric', ['type' => 'index', 'columns' => ['physical_spec_id', 'numeric_value']]);
    $table->addIndex('idx_cord_physical_specs_text', ['type' => 'index', 'columns' => ['physical_spec_id', 'spec_value']]);
    $table->addConstraint('idx_cord_physical_specs_unique', ['type' => 'unique', 'columns' => ['product_id', 'physical_spec_id']]);

    return $table;
};
