<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for models_images table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('model', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('foreign_key', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('image_id', 'uuid', [
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

    $table->addIndex('model_foreign_key', ['type' => 'index', 'columns' => ['model', 'foreign_key']]);
    $table->addIndex('image_id', ['type' => 'index', 'columns' => ['image_id']]);

    return $table;
};
