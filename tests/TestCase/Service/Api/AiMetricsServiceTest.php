<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\AiMetricsService;
use Cake\TestSuite\TestCase;

class AiMetricsServiceTest extends TestCase
{
    public function testCalculateGoogleTranslateCost(): void
    {
        $svc = new AiMetricsService();
        $this->assertEqualsWithDelta(0.00002, $svc->calculateGoogleTranslateCost(1), 0.0000001);
        $this->assertEqualsWithDelta(0.02, $svc->calculateGoogleTranslateCost(1000), 0.0000001);
        $this->assertEqualsWithDelta(20.0, $svc->calculateGoogleTranslateCost(1000000), 0.0000001);
    }

    public function testCountCharacters(): void
    {
        $svc = new AiMetricsService();
        $count = $svc->countCharacters(['Hello', '世界', null, '']);
        // 'Hello' = 5, '世界' = 2
        $this->assertSame(7, $count);
    }
}
