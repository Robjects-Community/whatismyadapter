<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for users table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('is_admin', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('role', 'string', [
        'length' => 32,
        'null' => false,
        'default' => 'user',
    ]);
    $table->addColumn('role_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('email', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('password', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('image', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('alt_text', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('keywords', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('dir', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('size', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('mime', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('username', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('active', 'boolean', [
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('reset_token', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('reset_token_expires', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('role', ['type' => 'index', 'columns' => ['role']]);
    $table->addIndex('role_id', ['type' => 'index', 'columns' => ['role_id']]);

    return $table;
};
