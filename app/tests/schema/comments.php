<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for comments table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('foreign_key', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('model', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('user_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('content', 'text', [
        'null' => false,
    ]);
    $table->addColumn('display', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('is_inappropriate', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('is_analyzed', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('inappropriate_reason', 'string', [
        'length' => 300,
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('created_by', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('modified_by', 'uuid', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
