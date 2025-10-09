<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for queue_configurations table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('config_key', 'string', [
        'length' => 50,
        'null' => false,
    ]);
    $table->addColumn('queue_type', 'string', [
        'length' => 20,
        'null' => false,
        'default' => 'redis',
    ]);
    $table->addColumn('queue_name', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('host', 'string', [
        'length' => 255,
        'null' => false,
        'default' => 'localhost',
    ]);
    $table->addColumn('port', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('username', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('password', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('db_index', 'integer', [
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('vhost', 'string', [
        'length' => 100,
        'null' => true,
        'default' => '/',
    ]);
    $table->addColumn('exchange', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('routing_key', 'string', [
        'length' => 100,
        'null' => true,
    ]);
    $table->addColumn('ssl_enabled', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('persistent', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('max_workers', 'integer', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('priority', 'integer', [
        'null' => false,
        'default' => '5',
    ]);
    $table->addColumn('enabled', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('config_data', 'json', [
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

    $table->addIndex('queue_type', ['type' => 'index', 'columns' => ['queue_type']]);
    $table->addIndex('enabled', ['type' => 'index', 'columns' => ['enabled']]);
    $table->addIndex('priority', ['type' => 'index', 'columns' => ['priority']]);
    $table->addConstraint('config_key', ['type' => 'unique', 'columns' => ['config_key']]);

    return $table;
};
