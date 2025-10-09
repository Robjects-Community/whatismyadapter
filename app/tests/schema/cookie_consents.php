<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for cookie_consents table
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
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('analytics_consent', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('functional_consent', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('marketing_consent', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('essential_consent', 'boolean', [
        'null' => false,
        'default' => '1',
    ]);
    $table->addColumn('ip_address', 'string', [
        'length' => 45,
        'null' => false,
    ]);
    $table->addColumn('user_agent', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('idx_session', ['type' => 'index', 'columns' => ['session_id']]);
    $table->addIndex('idx_user', ['type' => 'index', 'columns' => ['user_id']]);

    return $table;
};
