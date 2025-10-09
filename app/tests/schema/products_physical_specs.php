<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_physical_specs table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('spec_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('spec_type', 'string', [
        'null' => true,
        'default' => 'text',
    ]);
    $table->addColumn('measurement_unit', 'string', [
        'length' => 20,
        'null' => true,
    ]);
    $table->addColumn('spec_description', 'text', [
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

    $table->addIndex('idx_physical_specs_type', ['type' => 'index', 'columns' => ['spec_type']]);
    $table->addConstraint('idx_physical_specs_name', ['type' => 'unique', 'columns' => ['spec_name']]);

    return $table;
};
