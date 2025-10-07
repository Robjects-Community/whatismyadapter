<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for blocked_ips table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('ip_address', 'string', [
        'length' => 45,
        'null' => false,
    ]);
    $table->addColumn('reason', 'text', [
        'null' => true,
    ]);
    $table->addColumn('blocked_at', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('expires_at', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
