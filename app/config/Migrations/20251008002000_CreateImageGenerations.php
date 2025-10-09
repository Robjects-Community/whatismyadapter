<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * CreateImageGenerations Migration
 * 
 * Creates the image_generations table for tracking AI-generated images
 */
class CreateImageGenerations extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('image_generations', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table
            ->addColumn('id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('prompt', 'text', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('model', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('image_url', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('image_path', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('width', 'integer', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('height', 'integer', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => 'pending',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('error_message', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('cost', 'decimal', [
                'precision' => 10,
                'scale' => 4,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('generation_time_ms', 'integer', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['user_id'])
            ->addIndex(['status'])
            ->addIndex(['created'])
            ->create();
    }
}
