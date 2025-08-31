<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SystemLogsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'level' => 'error',
                'message' => 'Database connection failed',
                'context' => json_encode(['error' => 'Connection refused']),
                'created' => '2024-09-22 08:55:00',
                'group_name' => 'database',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'level' => 'warning',
                'message' => 'Slow query detected',
                'context' => json_encode(['query' => 'SELECT * FROM large_table', 'execution_time' => '5.2s']),
                'created' => '2024-09-22 09:15:00',
                'group_name' => 'performance',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'level' => 'info',
                'message' => 'User login successful',
                'context' => json_encode(['user_id' => 123, 'ip_address' => '192.168.1.1']),
                'created' => '2024-09-22 10:00:00',
                'group_name' => 'authentication',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440003',
                'level' => 'error',
                'message' => 'Payment gateway error',
                'context' => json_encode(['error_code' => 'PG001', 'transaction_id' => 'TX12345']),
                'created' => '2024-09-23 11:30:00',
                'group_name' => 'payment',
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440004',
                'level' => 'debug',
                'message' => 'Cache cleared successfully',
                'context' => json_encode(['cache_type' => 'redis', 'keys_cleared' => 150]),
                'created' => '2024-09-23 14:45:00',
                'group_name' => 'system',
            ],
        ];
        parent::init();
    }
}
