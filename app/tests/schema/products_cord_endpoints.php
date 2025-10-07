<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_cord_endpoints table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('port_type_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('endpoint_position', 'string', [
        'null' => false,
    ]);
    $table->addColumn('is_detachable', 'boolean', [
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('adapter_functionality', 'text', [
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

    $table->addIndex('idx_cord_endpoints_port_type', ['type' => 'index', 'columns' => ['port_type_id', 'product_id']]);
    $table->addIndex('idx_cord_endpoints_detachable', ['type' => 'index', 'columns' => ['is_detachable']]);
    $table->addConstraint('idx_cord_endpoints_unique_position', ['type' => 'unique', 'columns' => ['product_id', 'port_type_id', 'endpoint_position']]);

    return $table;
};
