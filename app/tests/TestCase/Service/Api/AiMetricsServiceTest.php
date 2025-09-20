<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Model\Table\AiMetricsTable;
use App\Service\Api\AiMetricsService;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
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

        // Create AI settings in the database (proper approach for tests)
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        // Create enableMetrics setting
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AI',
            'key_name' => 'enableMetrics',
            'value' => 1,
            'value_type' => 'bool',
        ]));

        // Create dailyCostLimit setting
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AI',
            'key_name' => 'dailyCostLimit',
            'value' => '2.50',
            'value_type' => 'numeric',
        ]));

        // Clear cache to ensure fresh settings
        Cache::clear(SettingsManager::getCacheConfig());
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

        // Clear cache
        Cache::clear(SettingsManager::getCacheConfig());

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
        // Clear existing data for isolated test
        $this->aiMetricsTable->deleteAll([]);

        // Create test metrics for today
        $today = FrozenTime::now();
        $metrics = [
            ['cost_usd' => 0.50, 'created' => $today],
            ['cost_usd' => 0.25, 'created' => $today],
            ['cost_usd' => 0.75, 'created' => $today->subDays(1)], // Yesterday
        ];

        foreach ($metrics as $data) {
            $entity = $this->aiMetricsTable->newEntity(array_merge([
                'task_type' => 'test',
                'execution_time_ms' => 100,
                'success' => true,
            ], $data));
            $this->aiMetricsTable->save($entity);
        }

        $dailyCost = $this->service->getDailyCost();
        $this->assertEquals(0.75, $dailyCost); // Only today's costs (0.50 + 0.25)
    }

    /**
     * Test isDailyCostLimitReached method
     *
     * @return void
     */
    public function testIsDailyCostLimitReached(): void
    {
        // Clear existing data for isolated test
        $this->aiMetricsTable->deleteAll([]);

        // Daily limit is set to $2.50 in setUp
        $this->assertFalse($this->service->isDailyCostLimitReached());

        // Add metrics to reach the limit
        $entity = $this->aiMetricsTable->newEntity([
            'task_type' => 'expensive_task',
            'execution_time_ms' => 100,
            'success' => true,
            'cost_usd' => 2.60, // Over the limit
            'created' => FrozenTime::now(),
        ]);
        $this->aiMetricsTable->save($entity);

        $this->assertTrue($this->service->isDailyCostLimitReached());
    }

    /**
     * Test getMetricsSummary method
     *
     * @return void
     */
    public function testGetMetricsSummary(): void
    {
        // Clear existing data for isolated test
        $this->aiMetricsTable->deleteAll([]);

        // Create test data
        $this->createTestMetrics();

        $summary = $this->service->getMetricsSummary();

        $this->assertArrayHasKey('totalCalls', $summary);
        $this->assertArrayHasKey('successRate', $summary);
        $this->assertArrayHasKey('totalCost', $summary);

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
        // Clear existing data for isolated test
        $this->aiMetricsTable->deleteAll([]);

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
        $this->markTestSkipped('Google API service integration requires external dependencies');

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
        $this->markTestSkipped('Anthropic API service integration requires API key configuration');

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
        // Clear existing data for isolated test
        $this->aiMetricsTable->deleteAll([]);

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
