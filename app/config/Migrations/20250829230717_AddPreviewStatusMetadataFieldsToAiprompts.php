<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddPreviewStatusMetadataFieldsToAiprompts extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('aiprompts');

        $table
            ->addColumn('status', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 50,
                'after' => 'temperature',
            ])
            ->addColumn('last_used', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'status',
            ])
            ->addColumn('usage_count', 'integer', [
                'null' => false,
                'default' => 0,
                'after' => 'last_used',
                'signed' => false,
            ])
            ->addColumn('success_rate', 'float', [
                'null' => true,
                'default' => null,
                'after' => 'usage_count',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'default' => null,
                'after' => 'success_rate',
            ])
            ->addColumn('preview_sample', 'text', [
                'null' => true,
                'default' => null,
                'after' => 'description',
            ])
            ->addColumn('expected_output', 'text', [
                'null' => true,
                'default' => null,
                'after' => 'preview_sample',
            ])
            ->addColumn('is_active', 'boolean', [
                'null' => false,
                'default' => true,
                'after' => 'expected_output',
            ])
            ->addColumn('category', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 100,
                'after' => 'is_active',
            ])
            ->addColumn('version', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 50,
                'after' => 'category',
            ])
            ->update();
    }
}
