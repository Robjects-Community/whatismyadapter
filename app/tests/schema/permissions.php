<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for permissions table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('resource', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('action', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
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

    $table->addIndex('resource_2', ['type' => 'index', 'columns' => ['resource']]);
    $table->addConstraint('resource', ['type' => 'unique', 'columns' => ['resource', 'action']]);

    return $table;
};
