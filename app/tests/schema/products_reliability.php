<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_reliability table
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
    $table->addColumn('total_score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => false,
        'default' => '0.00',
    ]);
    $table->addColumn('completeness_percent', 'decimal', [
        'length' => 5,
        'precision' => 2,
        'null' => false,
        'default' => '0.00',
    ]);
    $table->addColumn('field_scores_json', 'json', [
        'null' => true,
    ]);
    $table->addColumn('scoring_version', 'string', [
        'length' => 32,
        'null' => false,
        'default' => 'v1',
    ]);
    $table->addColumn('last_source', 'string', [
        'length' => 20,
        'null' => false,
        'default' => 'system',
    ]);
    $table->addColumn('last_calculated', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('updated_by_user_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('updated_by_service', 'string', [
        'length' => 100,
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
        'columns' => ['id']
    ]);

    $table->addIndex('idx_rel_total_score', ['type' => 'index', 'columns' => ['total_score']]);
    $table->addIndex('idx_rel_model', ['type' => 'index', 'columns' => ['model']]);
    $table->addIndex('idx_rel_fk', ['type' => 'index', 'columns' => ['foreign_key']]);
    $table->addIndex('idx_rel_last_calculated', ['type' => 'index', 'columns' => ['last_calculated']]);
    $table->addIndex('idx_rel_updated_by_user', ['type' => 'index', 'columns' => ['updated_by_user_id']]);
    $table->addConstraint('idx_rel_model_fk', ['type' => 'unique', 'columns' => ['model', 'foreign_key']]);

    return $table;
};
