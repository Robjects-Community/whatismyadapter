<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for quiz_submissions table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('session_id', 'string', [
        'length' => 64,
        'null' => false,
    ]);
    $table->addColumn('quiz_type', 'string', [
        'length' => 20,
        'null' => false,
        'default' => 'comprehensive',
    ]);
    $table->addColumn('answers', 'json', [
        'null' => false,
    ]);
    $table->addColumn('matched_product_ids', 'json', [
        'null' => true,
    ]);
    $table->addColumn('confidence_scores', 'json', [
        'null' => true,
    ]);
    $table->addColumn('result_summary', 'text', [
        'null' => true,
    ]);
    $table->addColumn('analytics', 'json', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => false,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => false,
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

    $table->addIndex('session_id', ['type' => 'index', 'columns' => ['session_id']]);
    $table->addIndex('quiz_type', ['type' => 'index', 'columns' => ['quiz_type']]);
    $table->addIndex('created', ['type' => 'index', 'columns' => ['created']]);
    $table->addIndex('user_id', ['type' => 'index', 'columns' => ['user_id']]);

    return $table;
};
