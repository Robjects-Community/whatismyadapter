<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_cord_categories table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('parent_category_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('category_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('category_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('category_icon', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('display_order', 'integer', [
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('is_active', 'boolean', [
        'null' => true,
        'default' => '1',
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

    $table->addIndex('idx_cord_categories_parent', ['type' => 'index', 'columns' => ['parent_category_id']]);
    $table->addIndex('idx_cord_categories_display_order', ['type' => 'index', 'columns' => ['display_order']]);
    $table->addIndex('idx_cord_categories_active', ['type' => 'index', 'columns' => ['is_active']]);

    return $table;
};
