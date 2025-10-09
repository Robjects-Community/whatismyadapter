<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for user_account_confirmations table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_id', 'string', [
        'length' => 36,
        'null' => false,
    ]);
    $table->addColumn('confirmation_code', 'string', [
        'length' => 36,
        'null' => false,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
        'default' => 'CURRENT_TIMESTAMP',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addConstraint('confirmation_code', ['type' => 'unique', 'columns' => ['confirmation_code']]);

    return $table;
};
