<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateAdaptersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('adapters');

        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('type', 'enum', [
            'values' => ['USB', 'HDMI', 'Ethernet', 'Audio', 'Video'],
            'null' => false,
        ]);

        $table->addColumn('gender', 'enum', [
            'values' => ['Male', 'Female'],
            'null' => false,
        ]);

        $table->addColumn('additional_params', 'json', [
            'null' => true,
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
