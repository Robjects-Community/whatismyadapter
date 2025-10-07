<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for email_templates table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('template_identifier', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('subject', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('body_html', 'text', [
        'null' => true,
    ]);
    $table->addColumn('body_plain', 'text', [
        'null' => true,
    ]);
    $table->addColumn('created', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('modified', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addConstraint('id', ['type' => 'unique', 'columns' => ['id']]);

    return $table;
};
