<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_cable_capabilities table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('capability_name', 'string', [
        'length' => 100,
        'null' => false,
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

    $table->addIndex('idx_cable_capabilities_category', ['type' => 'index', 'columns' => ['capability_category']]);
    $table->addConstraint('idx_cable_capabilities_name', ['type' => 'unique', 'columns' => ['capability_name']]);

    return $table;
};
