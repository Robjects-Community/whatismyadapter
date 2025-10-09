<?php
declare(strict_types=1);
use Cake\Utility\Text;
use Migrations\AbstractMigration;
class InsertAiMetricsSettings extends AbstractMigration
{
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 8,
                'category' => 'AI',
                'key_name' => 'hourlyLimit',
                'value' => '100',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum number of AI API calls allowed per hour. This helps control costs and prevents runaway usage. Set to 0 for unlimited (not recommended for production).',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 9,
                'category' => 'AI',
                'key_name' => 'dailyCostLimit',
                'value' => '2.50',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum daily cost threshold in USD for AI operations. When this limit is reached, AI features will be temporarily disabled until the next day. This prevents unexpected billing charges.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 10,
                'category' => 'AI',
                'key_name' => 'enableMetrics',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable detailed tracking and analytics for AI operations. This includes execution times, token usage, costs, and success rates. Metrics help optimize performance and monitor API usage patterns.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 11,
                'category' => 'AI',
                'key_name' => 'enableCostAlerts',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Send email notifications when AI costs approach or exceed defined thresholds. Alerts help administrators monitor spending and take action before limits are reached.',
                'data' => null,
                'column_width' => 2,
            ])
            ->save();
    }
}