<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AiMetricsFixture
 */
class AiMetricsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'task_type' => 'summarize',
                'execution_time_ms' => 250,
                'tokens_used' => 120,
                'cost_usd' => 0.500000,
                'success' => 1,
                'error_message' => null,
                'model_used' => 'gpt-4o-mini',
                'created' => '2025-08-10 12:00:00',
                'modified' => '2025-08-10 12:00:00',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'task_type' => 'summarize',
                'execution_time_ms' => 300,
                'tokens_used' => 140,
                'cost_usd' => 0.200000,
                'success' => 0,
                'error_message' => 'API rate limit exceeded',
                'model_used' => 'gpt-4o-mini',
                'created' => '2025-08-11 15:30:00',
                'modified' => '2025-08-11 15:30:00',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'task_type' => 'translate',
                'execution_time_ms' => 800,
                'tokens_used' => 500,
                'cost_usd' => 1.300000,
                'success' => 1,
                'error_message' => null,
                'model_used' => 'gpt-4o',
                'created' => '2025-08-12 09:45:00',
                'modified' => '2025-08-12 09:45:00',
            ],
            [
                'id' => '44444444-4444-4444-4444-444444444444',
                'task_type' => 'translate',
                'execution_time_ms' => 1200,
                'tokens_used' => 800,
                'cost_usd' => 2.500000,
                'success' => 0,
                'error_message' => 'Provider timeout',
                'model_used' => 'gpt-4o',
                'created' => '2025-07-01 08:00:00',
                'modified' => '2025-07-01 08:00:00',
            ],
            [
                'id' => '55555555-5555-5555-5555-555555555555',
                'task_type' => 'classify',
                'execution_time_ms' => 100,
                'tokens_used' => 60,
                'cost_usd' => 0.050000,
                'success' => 1,
                'error_message' => null,
                'model_used' => 'gpt-4o-mini',
                'created' => '2025-08-13 18:20:00',
                'modified' => '2025-08-13 18:20:00',
            ],
            [
                'id' => '66666666-6666-6666-6666-666666666666',
                'task_type' => 'classify',
                'execution_time_ms' => 150,
                'tokens_used' => 80,
                'cost_usd' => 0.070000,
                'success' => 1,
                'error_message' => null,
                'model_used' => 'gpt-4o-mini',
                'created' => '2025-06-15 10:00:00',
                'modified' => '2025-06-15 10:00:00',
            ],
        ];
        parent::init();
    }
}
