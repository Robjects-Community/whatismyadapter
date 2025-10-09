<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_reliability_fields table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('model', 'string', [
        'length' => 20,
        'null' => false,
    ]);
    $table->addColumn('foreign_key', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('field', 'string', [
        'length' => 64,
        'null' => false,
    ]);
    $table->addColumn('score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => false,
        'default' => '0.00',
    ]);
    $table->addColumn('weight', 'decimal', [
        'length' => 4,
        'precision' => 3,
        'null' => false,
        'default' => '0.000',
    ]);
    $table->addColumn('max_score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => false,
        'default' => '1.00',
    ]);
    $table->addColumn('notes', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['model', 'foreign_key', 'field']
    ]);

    $table->addIndex('idx_prf_model_fk', ['type' => 'index', 'columns' => ['model', 'foreign_key']]);
    $table->addIndex('idx_prf_field', ['type' => 'index', 'columns' => ['field']]);

    return $table;
};
