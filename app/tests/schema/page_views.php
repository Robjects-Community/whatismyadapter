<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for page_views table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('article_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('ip_address', 'string', [
        'length' => 45,
        'null' => false,
    ]);
    $table->addColumn('user_agent', 'text', [
        'null' => true,
    ]);
    $table->addColumn('referer', 'text', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
