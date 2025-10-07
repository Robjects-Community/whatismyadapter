<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for aiprompts table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('task_type', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('system_prompt', 'text', [
        'null' => false,
    ]);
    $table->addColumn('model', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('max_tokens', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('temperature', 'float', [
        'null' => false,
    ]);
    $table->addColumn('status', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('last_used', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('usage_count', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('success_rate', 'float', [
        'null' => true,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('preview_sample', 'text', [
        'null' => true,
    ]);
    $table->addColumn('expected_output', 'text', [
        'null' => true,
    ]);
    $table->addColumn('is_active', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('category', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('version', 'string', [
        'length' => 50,
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
