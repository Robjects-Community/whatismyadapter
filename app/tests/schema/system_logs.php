<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for system_logs table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('level', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('message', 'text', [
        'null' => false,
    ]);
    $table->addColumn('context', 'text', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('group_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
