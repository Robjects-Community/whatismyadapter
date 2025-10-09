<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for internationalisations table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('locale', 'string', [
        'length' => 10,
        'null' => false,
    ]);
    $table->addColumn('message_id', 'text', [
        'null' => false,
    ]);
    $table->addColumn('message_str', 'text', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
