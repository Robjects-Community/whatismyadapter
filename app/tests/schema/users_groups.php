<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for users_groups table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_group_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('user_id_2', ['type' => 'index', 'columns' => ['user_id']]);
    $table->addIndex('user_group_id', ['type' => 'index', 'columns' => ['user_group_id']]);
    $table->addConstraint('user_id', ['type' => 'unique', 'columns' => ['user_id', 'user_group_id']]);

    return $table;
};
