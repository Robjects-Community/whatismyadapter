<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for slugs table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('model', 'string', [
        'length' => 20,
        'null' => false,
    ]);
    $table->addColumn('foreign_key', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('created', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addIndex('idx_slugs_lookup', ['type' => 'index', 'columns' => ['model', 'slug']]);
    $table->addIndex('idx_slugs_foreign', ['type' => 'index', 'columns' => ['model', 'foreign_key']]);
    $table->addConstraint('id', ['type' => 'unique', 'columns' => ['id']]);

    return $table;
};
