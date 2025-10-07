<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\AiMetricsService;
use Cake\TestSuite\TestCase;

class AiMetricsServiceMoreTest extends TestCase
{
    protected array $fixtures = ['app.AiMetrics'];

    public function testGetMetricsSummary(): void
    {
        $svc = new AiMetricsService();
        $summary = $svc->getMetricsSummary();
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('totalCalls', $summary);
        $this->assertArrayHasKey('successRate', $summary);
        $this->assertArrayHasKey('totalCost', $summary);

        // From fixture, total rows = 6
        $this->assertGreaterThanOrEqual(6, (int)$summary['totalCalls']);
    }

    public function testIsDailyCostLimitReachedDefaultFalse(): void
    {
        $svc = new AiMetricsService();
        // Fixture created dates are in 2025-08-*, not today, so today's cost is 0
        $this->assertFalse($svc->isDailyCostLimitReached());
    }
}
