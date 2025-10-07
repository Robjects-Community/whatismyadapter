<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for roles_permissions table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('role_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('permission_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('role_id_2', ['type' => 'index', 'columns' => ['role_id']]);
    $table->addIndex('permission_id', ['type' => 'index', 'columns' => ['permission_id']]);
    $table->addConstraint('role_id', ['type' => 'unique', 'columns' => ['role_id', 'permission_id']]);

    return $table;
};
