<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_reliability_logs table
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
    $table->addColumn('from_total_score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('to_total_score', 'decimal', [
        'length' => 3,
        'precision' => 2,
        'null' => false,
    ]);
    $table->addColumn('from_field_scores_json', 'json', [
        'null' => true,
    ]);
    $table->addColumn('to_field_scores_json', 'json', [
        'null' => false,
    ]);
    $table->addColumn('source', 'string', [
        'length' => 20,
        'null' => false,
    ]);
    $table->addColumn('actor_user_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('actor_service', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('message', 'text', [
        'null' => true,
    ]);
    $table->addColumn('checksum_sha256', 'string', [
        'length' => 64,
        'null' => false,
        'fixed' => true,  // Indicates fixed-length string (CHAR equivalent)
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('idx_prl_model_fk_created', ['type' => 'index', 'columns' => ['model', 'foreign_key', 'created']]);
    $table->addIndex('idx_prl_source', ['type' => 'index', 'columns' => ['source']]);
    $table->addIndex('idx_prl_actor_user', ['type' => 'index', 'columns' => ['actor_user_id']]);

    return $table;
};
