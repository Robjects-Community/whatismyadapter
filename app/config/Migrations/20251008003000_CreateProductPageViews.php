<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateProductPageViews Migration
 * 
 * Creates the product_page_views table for tracking product analytics
 */
class CreateProductPageViews extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('product_page_views', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('ip_address', 'string', [
                'default' => null,
                'limit' => 45,
                'null' => true,
                'comment' => 'IPv4 or IPv6 address',
            ])
            ->addColumn('user_agent', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('referer', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('session_id', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['product_id'])
            ->addIndex(['created'])
            ->addIndex(['session_id'])
            ->create();
    }
}
