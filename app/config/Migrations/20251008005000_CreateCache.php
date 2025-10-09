<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateCache Migration
 * 
 * Creates the cache table for database-backed caching
 * Note: CakePHP typically uses file or Redis caching, but this provides a DB option
 */
class CreateCache extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('cache', [
            'id' => false,
            'primary_key' => ['key'],
        ]);

        $table
            ->addColumn('key', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('expires', 'integer', [
                'default' => null,
                'null' => false,
                'comment' => 'Unix timestamp for expiration',
            ])
            ->addIndex(['expires'])
            ->create();
    }
}
