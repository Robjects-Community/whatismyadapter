<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateProductsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('products');

        $table->addColumn('name', 'string', [
            'limit' => 255,
            'null' => false,
        ]);

        $table->addColumn('description', 'text', [
            'null' => true,
        ]);

        $table->addColumn('link_to_buy', 'string', [
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
