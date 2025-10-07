<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_tags table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('tag_id', 'uuid', [
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['product_id', 'tag_id']
    ]);

    $table->addIndex('idx_products_tags_product', ['type' => 'index', 'columns' => ['product_id']]);
    $table->addIndex('idx_products_tags_tag', ['type' => 'index', 'columns' => ['tag_id']]);

    return $table;
};
