<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateHomepageFeeds Migration
 * 
 * Creates the homepage_feeds table for managing dynamic homepage feed configurations
 */
class CreateHomepageFeeds extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('homepage_feeds', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('feed_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('position', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'null' => false,
            ])
            ->addColumn('settings', 'text', [
                'default' => null,
                'null' => true,
                'comment' => 'JSON configuration for feed',
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['feed_type'])
            ->addIndex(['position'])
            ->addIndex(['is_active'])
            ->create();
    }
}
