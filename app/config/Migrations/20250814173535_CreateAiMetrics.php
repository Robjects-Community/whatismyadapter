<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateAiMetrics extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('ai_metrics', [
            'id' => false,
            'primary_key' => ['id'],
        ]);
        
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('task_type', 'string', [
            'default' => null,
            'limit' => 50,
            'null' => false,
        ])
        ->addColumn('execution_time_ms', 'integer', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('tokens_used', 'integer', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('cost_usd', 'decimal', [
            'precision' => 10,
            'scale' => 6,
            'default' => null,
            'null' => true,
        ])
        ->addColumn('success', 'boolean', [
            'default' => true,
            'null' => false,
        ])
        ->addColumn('error_message', 'text', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('model_used', 'string', [
            'default' => null,
            'limit' => 50,
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
        ->addIndex(['task_type'])
        ->addIndex(['created'])
        ->addIndex(['success'])
        ->create();
    }
    public function down(): void
    {
        $this->table('ai_metrics')->drop()->save();
    }
}