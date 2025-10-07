<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_use_case_scenarios table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('scenario_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('scenario_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('cord_category_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('required_capabilities', 'json', [
        'null' => true,
    ]);
    $table->addColumn('preferred_length_range', 'string', [
        'length' => 50,
        'null' => true,
    ]);
    $table->addColumn('environment_suitability', 'string', [
        'null' => true,
        'default' => 'Indoor',
    ]);
    $table->addColumn('priority_factors', 'json', [
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

    $table->addIndex('idx_use_case_scenarios_category', ['type' => 'index', 'columns' => ['cord_category_id']]);
    $table->addIndex('idx_use_case_scenarios_environment', ['type' => 'index', 'columns' => ['environment_suitability']]);
    $table->addConstraint('idx_use_case_scenarios_name', ['type' => 'unique', 'columns' => ['scenario_name']]);

    return $table;
};
