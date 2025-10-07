<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for roles table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('is_system', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('priority', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('priority', ['type' => 'index', 'columns' => ['priority']]);
    $table->addConstraint('slug', ['type' => 'unique', 'columns' => ['slug']]);

    return $table;
};
