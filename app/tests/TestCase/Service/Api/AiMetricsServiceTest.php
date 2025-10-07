<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\AiMetricsService;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;

class AiMetricsServiceTest extends TestCase
{
    protected array $fixtures = ['app.AiMetrics'];

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

    public function testGetMetricsSummaryTotals(): void
    {
        $svc = new AiMetricsService();
        $summary = $svc->getMetricsSummary();

        $this->assertSame(4, $summary['totalCalls']);
        $this->assertEqualsWithDelta(2.05, $summary['totalCost'], 0.0001);
        $this->assertEqualsWithDelta(75.0, $summary['successRate'], 0.01);
    }

    public function testGetTaskTypeStatisticsStructure(): void
    {
        $svc = new AiMetricsService();
        $stats = $svc->getTaskTypeStatistics();

        $this->assertArrayHasKey('summarize', $stats);
        $this->assertArrayHasKey('translate', $stats);
        $this->assertArrayHasKey('classify', $stats);

        $this->assertArrayHasKey('count', $stats['summarize']);
        $this->assertArrayHasKey('avg_time', $stats['summarize']);
        $this->assertArrayHasKey('success_rate', $stats['summarize']);
        $this->assertArrayHasKey('total_cost', $stats['summarize']);
        $this->assertArrayHasKey('total_tokens', $stats['summarize']);
    }

    public function testGetDailyCostWithFrozenTime(): void
    {
        $svc = new AiMetricsService();

        // 2025-08-10 has one record at cost 0.5
        FrozenTime::setTestNow(new FrozenTime('2025-08-10 12:00:00'));
        $this->assertEqualsWithDelta(0.5, $svc->getDailyCost(), 0.0001);

        // 2025-08-11 has one record at cost 0.2
        FrozenTime::setTestNow(new FrozenTime('2025-08-11 09:00:00'));
        $this->assertEqualsWithDelta(0.2, $svc->getDailyCost(), 0.0001);

        // A day with no activity
        FrozenTime::setTestNow(new FrozenTime('2025-09-01 00:00:00'));
        $this->assertEqualsWithDelta(0.0, $svc->getDailyCost(), 0.0001);

        // Reset test now
        FrozenTime::setTestNow();
    }
}
