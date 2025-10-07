<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for product_form_fields table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('field_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('field_label', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('field_type', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('field_placeholder', 'text', [
        'null' => true,
    ]);
    $table->addColumn('field_help_text', 'text', [
        'null' => true,
    ]);
    $table->addColumn('field_options', 'json', [
        'null' => true,
    ]);
    $table->addColumn('field_validation', 'json', [
        'null' => true,
    ]);
    $table->addColumn('field_group', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('display_order', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('column_width', 'integer', [
        'null' => false,
        'default' => '12',
    ]);
    $table->addColumn('is_required', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('is_active', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('ai_enabled', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('ai_prompt_template', 'text', [
        'null' => true,
    ]);
    $table->addColumn('ai_field_mapping', 'json', [
        'null' => true,
    ]);
    $table->addColumn('conditional_logic', 'json', [
        'null' => true,
    ]);
    $table->addColumn('default_value', 'text', [
        'null' => true,
    ]);
    $table->addColumn('css_classes', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('html_attributes', 'json', [
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

    $table->addIndex('field_group', ['type' => 'index', 'columns' => ['field_group', 'display_order']]);
    $table->addIndex('is_active', ['type' => 'index', 'columns' => ['is_active']]);
    $table->addIndex('ai_enabled', ['type' => 'index', 'columns' => ['ai_enabled']]);
    $table->addConstraint('field_name', ['type' => 'unique', 'columns' => ['field_name']]);

    return $table;
};
