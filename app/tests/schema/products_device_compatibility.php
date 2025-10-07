<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_device_compatibility table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('device_category', 'string', [
        'length' => 50,
        'null' => false,
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
        'null' => false,
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

    $table->addIndex('idx_device_compatibility_product', ['type' => 'index', 'columns' => ['product_id']]);
    $table->addIndex('idx_device_compatibility_device', ['type' => 'index', 'columns' => ['device_category', 'device_brand']]);
    $table->addIndex('idx_device_compatibility_level', ['type' => 'index', 'columns' => ['compatibility_level']]);
    $table->addIndex('idx_device_compatibility_rating', ['type' => 'index', 'columns' => ['performance_rating']]);

    return $table;
};
