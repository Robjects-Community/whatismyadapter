<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Model\Table\AiMetricsTable;
use App\Service\Api\AiMetricsService;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * App\Service\Api\AiMetricsService Test Case
 *
 * Tests comprehensive AI metrics functionality including:
 * - Metrics recording to database
 * - Cost calculation for various services
 * - Daily cost tracking and limits
 * - Integration with AI services
 * - Rate limiting and monitoring
 */
class AiMetricsServiceTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AiMetrics',
        'app.Settings',
    ];

    /**
     * Test subject
     *
     * @var \App\Service\Api\AiMetricsService
     */
    protected AiMetricsService $service;

    /**
     * AI Metrics table
     *
     * @var \App\Model\Table\AiMetricsTable
     */
    protected AiMetricsTable $aiMetricsTable;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        FrozenTime::setTestNow('2024-01-01 12:00:00');

        $this->service = new AiMetricsService();
        $this->aiMetricsTable = TableRegistry::getTableLocator()->get('AiMetrics');

        // Set up test settings
        SettingsManager::write('AI.enableMetrics', true);
        SettingsManager::write('AI.dailyCostLimit', 2.50);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->service);
        unset($this->aiMetricsTable);

        // Reset frozen time
        FrozenTime::setTestNow(null);

        parent::tearDown();
    }

    /**
     * Test recordMetrics method
     *
     * @return void
     */
    public function testRecordMetrics(): void
    {
        $result = $this->service->recordMetrics(
            'test_task',
            150,
            true,
            null,
            100,
            0.005,
            'test-model',
        );

        $this->assertTrue($result);

        // Verify record was created in database
        $metric = $this->aiMetricsTable->find()
            ->where(['task_type' => 'test_task'])
            ->first();

        $this->assertNotNull($metric);
        $this->assertEquals('test_task', $metric->task_type);
        $this->assertEquals(150, $metric->execution_time_ms);
        $this->assertTrue($metric->success);
        $this->assertNull($metric->error_message);
        $this->assertEquals(100, $metric->tokens_used);
        $this->assertEquals(0.005, $metric->cost_usd);
        $this->assertEquals('test-model', $metric->model_used);
    }

    /**
     * Test recordMetrics with error
     *
     * @return void
     */
    public function testRecordMetricsWithError(): void
    {
        $result = $this->service->recordMetrics(
            'failed_task',
            500,
            false,
            'API rate limit exceeded',
            0,
            0,
            'test-model',
        );

        $this->assertTrue($result);

        // Verify error was recorded
        $metric = $this->aiMetricsTable->find()
            ->where(['task_type' => 'failed_task'])
            ->first();

        $this->assertNotNull($metric);
        $this->assertFalse($metric->success);
        $this->assertEquals('API rate limit exceeded', $metric->error_message);
        $this->assertEquals(0, $metric->tokens_used);
        $this->assertEquals(0, $metric->cost_usd);
    }

    /**
     * Test calculateGoogleTranslateCost method
     *
     * @return void
     */
    public function testCalculateGoogleTranslateCost(): void
    {
        // Google Translate pricing: $20 per million characters
        $cost = $this->service->calculateGoogleTranslateCost(1000);
        $this->assertEquals(0.02, $cost);

        $cost = $this->service->calculateGoogleTranslateCost(50000);
        $this->assertEquals(1.0, $cost);

        $cost = $this->service->calculateGoogleTranslateCost(0);
        $this->assertEquals(0, $cost);
    }

    /**
     * Test calculateAnthropicCost method
     *
     * @return void
     */
    public function testCalculateAnthropicCost(): void
    {
        // Anthropic pricing: Input $3/million, Output $15/million tokens
        $reflection = new ReflectionClass($this->service);
        if ($reflection->hasMethod('calculateAnthropicCost')) {
            $method = $reflection->getMethod('calculateAnthropicCost');
            $method->setAccessible(true);

            // Test with input and output tokens
            $cost = $method->invoke($this->service, 1000, 500);
            $expectedCost = (1000 * 3 / 1000000) + (500 * 15 / 1000000);
            $this->assertEquals($expectedCost, $cost);

            // Test with only input tokens
            $cost = $method->invoke($this->service, 2000, 0);
            $expectedCost = 2000 * 3 / 1000000;
            $this->assertEquals($expectedCost, $cost);
        } else {
            $this->markTestSkipped('calculateAnthropicCost method not found');
        }
    }

    /**
     * Test getDailyCost method
     *
     * @return void
     */
    public function testGetDailyCost(): void
    {
        // Create some test metrics for today
        $this->aiMetricsTable->save($this->aiMetricsTable->newEntity([
            'task_type' => 'test1',
            'execution_time_ms' => 100,
            'success' => true,
            'cost_usd' => 0.50,
            'created' => FrozenTime::now(),
        ]));

        $this->aiMetricsTable->save($this->aiMetricsTable->newEntity([
            'task_type' => 'test2',
            'execution_time_ms' => 200,
            'success' => true,
            'cost_usd' => 0.75,
            'created' => FrozenTime::now(),
        ]));

        // Create a metric from yesterday (should not be included)
        $this->aiMetricsTable->save($this->aiMetricsTable->newEntity([
            'task_type' => 'test3',
            'execution_time_ms' => 150,
            'success' => true,
            'cost_usd' => 1.00,
            'created' => FrozenTime::now()->subDays(1),
        ]));

        $dailyCost = $this->service->getDailyCost();
        $this->assertEquals(1.25, $dailyCost);
    }

    /**
     * Test isDailyCostLimitReached method
     *
     * @return void
     */
    public function testIsDailyCostLimitReached(): void
    {
        // Set daily limit to $1.00
        SettingsManager::write('AI.dailyCostLimit', 1.00);

        // Initially should not be reached
        $this->assertFalse($this->service->isDailyCostLimitReached());

        // Add metrics to exceed limit
        $this->aiMetricsTable->save($this->aiMetricsTable->newEntity([
            'task_type' => 'expensive_task',
            'execution_time_ms' => 1000,
            'success' => true,
            'cost_usd' => 1.50,
            'created' => FrozenTime::now(),
        ]));

        // Now limit should be reached
        $this->assertTrue($this->service->isDailyCostLimitReached());

        // Test with metrics disabled
        SettingsManager::write('AI.enableMetrics', false);
        $this->assertFalse($this->service->isDailyCostLimitReached());
    }

    /**
     * Test getMetricsSummary method
     *
     * @return void
     */
    public function testGetMetricsSummary(): void
    {
        // Create test metrics
        $this->createTestMetrics();

        $summary = $this->service->getMetricsSummary('1h');

        $this->assertArrayHasKey('totalCalls', $summary);
        $this->assertArrayHasKey('successRate', $summary);
        $this->assertArrayHasKey('totalCost', $summary);
        $this->assertArrayHasKey('avgExecutionTime', $summary);
        $this->assertArrayHasKey('taskBreakdown', $summary);

        $this->assertEquals(3, $summary['totalCalls']);
        $this->assertEquals(66.67, round($summary['successRate'], 2));
        $this->assertEquals(0.30, $summary['totalCost']);
    }

    /**
     * Test getRealtimeData method
     *
     * @return void
     */
    public function testGetRealtimeData(): void
    {
        // Create test metrics
        $this->createTestMetrics();

        $data = $this->service->getRealtimeData('24h');

        $this->assertArrayHasKey('metrics', $data);
        $this->assertArrayHasKey('rateLimit', $data);
        $this->assertArrayHasKey('queueStatus', $data);
        $this->assertArrayHasKey('recentActivity', $data);

        $metrics = $data['metrics'];
        $this->assertEquals(3, $metrics['totalCalls']);
        $this->assertGreaterThan(0, $metrics['successRate']);
    }

    /**
     * Test integration with GoogleApiService
     *
     * @return void
     */
    public function testGoogleApiServiceIntegration(): void
    {
        $googleService = new GoogleApiService();

        // Check if service has metrics integration
        $reflection = new ReflectionClass($googleService);
        $this->assertTrue($reflection->hasProperty('metricsService'));

        // Test that service can record metrics
        $property = $reflection->getProperty('metricsService');
        $property->setAccessible(true);
        $metricsService = $property->getValue($googleService);

        $this->assertInstanceOf(AiMetricsService::class, $metricsService);
    }

    /**
     * Test integration with AnthropicApiService
     *
     * @return void
     */
    public function testAnthropicApiServiceIntegration(): void
    {
        $anthropicService = new AnthropicApiService();

        // Check if service has metrics recording capability
        $reflection = new ReflectionClass($anthropicService);
        $hasMetricsIntegration = $reflection->hasMethod('recordMetrics') ||
                                 $reflection->hasProperty('metricsService');

        $this->assertTrue($hasMetricsIntegration, 'AnthropicApiService should have metrics integration');
    }

    /**
     * Test task type statistics
     *
     * @return void
     */
    public function testGetTaskTypeStatistics(): void
    {
        // Create metrics for different task types
        $taskTypes = [
            'google_translate' => ['count' => 5, 'cost' => 0.05],
            'anthropic_seo' => ['count' => 3, 'cost' => 0.15],
            'anthropic_summary' => ['count' => 2, 'cost' => 0.10],
        ];

        foreach ($taskTypes as $type => $data) {
            for ($i = 0; $i < $data['count']; $i++) {
                $this->aiMetricsTable->save($this->aiMetricsTable->newEntity([
                    'task_type' => $type,
                    'execution_time_ms' => rand(100, 500),
                    'success' => true,
                    'cost_usd' => $data['cost'] / $data['count'],
                    'created' => FrozenTime::now(),
                ]));
            }
        }

        $stats = $this->service->getTaskTypeStatistics();

        $this->assertCount(3, $stats);
        $this->assertEquals(5, $stats['google_translate']['count']);
        $this->assertEquals(0.05, $stats['google_translate']['total_cost']);
    }

    /**
     * Helper method to create test metrics
     *
     * @return void
     */
    protected function createTestMetrics(): void
    {
        $metrics = [
            [
                'task_type' => 'test_success',
                'execution_time_ms' => 100,
                'success' => true,
                'cost_usd' => 0.10,
                'tokens_used' => 50,
                'model_used' => 'test-model',
                'created' => FrozenTime::now(),
            ],
            [
                'task_type' => 'test_success',
                'execution_time_ms' => 150,
                'success' => true,
                'cost_usd' => 0.15,
                'tokens_used' => 75,
                'model_used' => 'test-model',
                'created' => FrozenTime::now()->subMinutes(30),
            ],
            [
                'task_type' => 'test_failure',
                'execution_time_ms' => 50,
                'success' => false,
                'error_message' => 'Test error',
                'cost_usd' => 0.05,
                'model_used' => 'test-model',
                'created' => FrozenTime::now()->subMinutes(45),
            ],
        ];

        foreach ($metrics as $data) {
            $this->aiMetricsTable->save($this->aiMetricsTable->newEntity($data));
        }
    }
}
