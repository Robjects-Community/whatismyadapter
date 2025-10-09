<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for settings table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('ordering', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('category', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('key_name', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('value', 'text', [
        'null' => true,
    ]);
    $table->addColumn('value_type', 'string', [
        'length' => 20,
        'null' => true,
        'default' => 'text',
    ]);
    $table->addColumn('value_obscure', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('data', 'text', [
        'null' => true,
    ]);
    $table->addColumn('column_width', 'integer', [
        'null' => false,
        'default' => '2',
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
