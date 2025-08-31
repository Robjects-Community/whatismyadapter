<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use DateTime;

class BlockedIpsFixture extends TestFixture
{
    public function init(): void
    {
        $now = new DateTime();
        $this->records = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'ip_address' => '192.168.1.1',
                'reason' => 'Suspicious activity detected',
                'blocked_at' => $now->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify('+1 day')->format('Y-m-d H:i:s'),
                'created' => $now->format('Y-m-d H:i:s'),
                'modified' => $now->format('Y-m-d H:i:s'),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'ip_address' => '10.0.0.5',
                'reason' => 'Multiple failed login attempts',
                'blocked_at' => $now->modify('-1 hour')->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify('+23 hours')->format('Y-m-d H:i:s'),
                'created' => $now->modify('-1 hour')->format('Y-m-d H:i:s'),
                'modified' => $now->format('Y-m-d H:i:s'),
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'ip_address' => '172.16.0.1',
                'reason' => 'Spam activity',
                'blocked_at' => $now->modify('-1 day')->format('Y-m-d H:i:s'),
                'expires_at' => $now->modify('+1 day')->format('Y-m-d H:i:s'),
                'created' => $now->format('Y-m-d H:i:s'),
                'modified' => $now->format('Y-m-d H:i:s'),
            ],
        ];
        parent::init();
    }
}
