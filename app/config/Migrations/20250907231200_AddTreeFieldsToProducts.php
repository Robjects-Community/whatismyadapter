<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddTreeFieldsToProducts extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('products');

        if (!$table->hasColumn('parent_id')) {
            $table->addColumn('parent_id', 'uuid', [
                'null' => true,
                'after' => 'article_id',
            ]);
        }
        if (!$table->hasColumn('lft')) {
            $table->addColumn('lft', 'integer', [
                'null' => true,
                'after' => 'parent_id',
            ]);
        }
        if (!$table->hasColumn('rght')) {
            $table->addColumn('rght', 'integer', [
                'null' => true,
                'after' => 'lft',
            ]);
        }
        if (!$table->hasColumn('kind')) {
            $table->addColumn('kind', 'string', [
                'null' => true,
                'limit' => 50,
                'after' => 'rght',
            ]);
        }

        $table->update();
    }
}

