<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateVideos Migration
 * 
 * Creates the videos table for managing video content
 * Note: Currently used for YouTube video integration
 */
class CreateVideos extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('videos', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => false,
                'comment' => 'Video URL or YouTube ID',
            ])
            ->addColumn('thumbnail', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('duration', 'integer', [
                'default' => null,
                'null' => true,
                'comment' => 'Duration in seconds',
            ])
            ->addColumn('is_published', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['is_published'])
            ->create();
    }
}
