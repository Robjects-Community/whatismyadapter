<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for images table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('alt_text', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('keywords', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('image', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('dir', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('size', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('mime', 'string', [
        'length' => 255,
        'null' => false,
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


    return $table;
};
