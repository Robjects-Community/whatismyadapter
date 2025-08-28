<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('email', 'string', [
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('password', 'string', [
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->addColumn('modified', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'update' => 'CURRENT_TIMESTAMP',
            'null' => false,
        ]);

        $table->create();
    }
}
