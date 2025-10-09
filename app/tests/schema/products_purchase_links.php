<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for products_purchase_links table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('product_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('store_url', 'string', [
        'length' => 500,
        'null' => false,
    ]);
    $table->addColumn('link_type', 'string', [
        'length' => 50,
        'null' => true,
        'default' => 'purchase',
    ]);
    $table->addColumn('retailer_name', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('listed_price', 'decimal', [
        'length' => 10,
        'precision' => 2,
        'null' => true,
    ]);
    $table->addColumn('price_currency', 'string', [
        'length' => 3,
        'null' => true,
        'fixed' => true,  // Indicates fixed-length string (CHAR equivalent)
    ]);
    $table->addColumn('last_price_check', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('link_status', 'string', [
        'null' => true,
        'default' => 'active',
    ]);
    $table->addColumn('affiliate_link', 'boolean', [
        'null' => true,
        'default' => '0',
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

    $table->addIndex('idx_purchase_links_product', ['type' => 'index', 'columns' => ['product_id']]);
    $table->addIndex('idx_purchase_links_type', ['type' => 'index', 'columns' => ['link_type']]);
    $table->addIndex('idx_purchase_links_retailer', ['type' => 'index', 'columns' => ['retailer_name']]);
    $table->addIndex('idx_purchase_links_status', ['type' => 'index', 'columns' => ['link_status']]);
    $table->addIndex('idx_purchase_links_price', ['type' => 'index', 'columns' => ['listed_price', 'price_currency']]);

    return $table;
};
