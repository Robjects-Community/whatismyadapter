<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for system_settings table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('namespace', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('setting_key', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('value_json', 'json', [
        'null' => false,
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

    $table->addIndex('idx_namespace', ['type' => 'index', 'columns' => ['namespace']]);
    $table->addConstraint('uniq_namespace_key', ['type' => 'unique', 'columns' => ['namespace', 'setting_key']]);

    return $table;
};
