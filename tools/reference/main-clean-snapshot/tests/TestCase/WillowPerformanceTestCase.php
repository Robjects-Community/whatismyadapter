<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\Cache\Cache;

/**
 * WillowPerformanceTestCase Base Class
 * 
 * Specialized base class for performance testing in WillowCMS.
 * Provides comprehensive performance validation utilities including:
 * - Response time benchmarking
 * - Database query analysis
 * - Memory usage monitoring
 * - Cache performance testing
 * - Load testing capabilities
 */
abstract class WillowPerformanceTestCase extends WillowControllerTestCase
{
    /**
     * Performance benchmarks
     * @var array
     */
    protected $benchmarks = [];

    /**
     * Query counter
     * @var array
     */
    protected $queryCount = [];

    /**
     * Memory usage tracking
     * @var array
     */
    protected $memoryUsage = [];

    /**
     * Performance thresholds
     * @var array
     */
    protected $performanceThresholds = [
        'response_time' => [
            'fast' => 100,      // < 100ms is fast
            'acceptable' => 500, // < 500ms is acceptable
            'slow' => 1000,      // > 1000ms is slow
        ],
        'memory' => [
            'low' => 16 * 1024 * 1024,    // 16MB
            'medium' => 64 * 1024 * 1024,  // 64MB
            'high' => 128 * 1024 * 1024,   // 128MB
        ],
        'queries' => [
            'few' => 10,         // < 10 queries is good
            'acceptable' => 25,  // < 25 queries is acceptable
            'many' => 50,        // > 50 queries is concerning
        ],
        'cache_hit_ratio' => [
            'excellent' => 90,   // > 90% hit ratio
            'good' => 75,        // > 75% hit ratio
            'poor' => 50,        // < 50% hit ratio
        ],
    ];

    /**
     * Setup method called before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $this->setupPerformanceTesting();
    }

    /**
     * Setup performance testing environment
     *
     * @return void
     */
    protected function setupPerformanceTesting(): void
    {
        // Enable query logging
        $connection = ConnectionManager::get('default');
        $connection->getDriver()->enableQueryLogging();
        
        // Clear cache for clean performance testing
        Cache::clear();
        
        // Reset tracking arrays
        $this->benchmarks = [];
        $this->queryCount = [];
        $this->memoryUsage = [];
    }

    /**
     * Start performance benchmark
     *
     * @param string $name Benchmark name
     * @return void
     */
    protected function startBenchmark(string $name): void
    {
        $this->benchmarks[$name] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'start_queries' => $this->getCurrentQueryCount(),
        ];
    }

    /**
     * End performance benchmark
     *
     * @param string $name Benchmark name
     * @return array Performance metrics
     */
    protected function endBenchmark(string $name): array
    {
        if (!isset($this->benchmarks[$name])) {
            $this->fail("Benchmark '{$name}' was not started");
        }

        $startData = $this->benchmarks[$name];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endQueries = $this->getCurrentQueryCount();

        $metrics = [
            'duration' => ($endTime - $startData['start_time']) * 1000, // Convert to milliseconds
            'memory_used' => $endMemory - $startData['start_memory'],
            'memory_peak' => memory_get_peak_usage(true),
            'queries_executed' => $endQueries - $startData['start_queries'],
            'start_time' => $startData['start_time'],
            'end_time' => $endTime,
        ];

        $this->benchmarks[$name] = array_merge($startData, $metrics);

        return $metrics;
    }

    /**
     * Assert response time is within acceptable limits
     *
     * @param string $url URL to test
     * @param string $threshold Threshold level (fast, acceptable, slow)
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array Performance metrics
     */
    protected function assertResponseTime(string $url, string $threshold = 'acceptable', string $method = 'GET', array $data = []): array
    {
        $benchmarkName = "response_time_{$method}_{$url}";
        $this->startBenchmark($benchmarkName);

        // Make the request
        switch (strtoupper($method)) {
            case 'GET':
                $this->get($url);
                break;
            case 'POST':
                $this->post($url, $data);
                break;
            case 'PUT':
                $this->put($url, $data);
                break;
            case 'DELETE':
                $this->delete($url);
                break;
        }

        $metrics = $this->endBenchmark($benchmarkName);
        $responseTime = $metrics['duration'];
        $maxTime = $this->performanceThresholds['response_time'][$threshold];

        $this->assertLessThan($maxTime, $responseTime,
            "Response time for {$method} {$url} should be less than {$maxTime}ms, got {$responseTime}ms");

        return $metrics;
    }

    /**
     * Assert memory usage is within acceptable limits
     *
     * @param callable $operation Operation to measure
     * @param string $threshold Memory threshold (low, medium, high)
     * @param string $operationName Name for the operation
     * @return array Memory metrics
     */
    protected function assertMemoryUsage(callable $operation, string $threshold = 'medium', string $operationName = 'operation'): array
    {
        $this->startBenchmark($operationName);
        
        // Execute the operation
        $result = $operation();
        
        $metrics = $this->endBenchmark($operationName);
        $memoryUsed = $metrics['memory_used'];
        $maxMemory = $this->performanceThresholds['memory'][$threshold];

        $this->assertLessThan($maxMemory, $memoryUsed,
            "Memory usage for {$operationName} should be less than " . 
            $this->formatBytes($maxMemory) . ", got " . $this->formatBytes($memoryUsed));

        return array_merge($metrics, ['operation_result' => $result]);
    }

    /**
     * Assert database query count is reasonable
     *
     * @param callable $operation Operation to measure
     * @param string $threshold Query count threshold (few, acceptable, many)
     * @param string $operationName Name for the operation
     * @return array Query metrics
     */
    protected function assertQueryCount(callable $operation, string $threshold = 'acceptable', string $operationName = 'operation'): array
    {
        $this->startBenchmark($operationName);
        
        // Execute the operation
        $result = $operation();
        
        $metrics = $this->endBenchmark($operationName);
        $queryCount = $metrics['queries_executed'];
        $maxQueries = $this->performanceThresholds['queries'][$threshold];

        $this->assertLessThan($maxQueries, $queryCount,
            "Query count for {$operationName} should be less than {$maxQueries}, got {$queryCount}");

        return array_merge($metrics, ['operation_result' => $result]);
    }

    /**
     * Test cache performance and hit ratios
     *
     * @param string $cacheKey Cache key to test
     * @param callable $dataGenerator Function to generate data if cache miss
     * @param string $threshold Hit ratio threshold
     * @return array Cache metrics
     */
    protected function assertCachePerformance(string $cacheKey, callable $dataGenerator, string $threshold = 'good'): array
    {
        $iterations = 100;
        $hits = 0;
        $totalTime = 0;

        // Clear cache to start fresh
        Cache::delete($cacheKey);

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            
            $data = Cache::remember($cacheKey, function() use ($dataGenerator) {
                return $dataGenerator();
            });
            
            $endTime = microtime(true);
            $totalTime += ($endTime - $startTime);

            // Check if it was a cache hit (data should be available quickly after first iteration)
            if ($i > 0 && ($endTime - $startTime) < 0.001) { // Less than 1ms indicates cache hit
                $hits++;
            }
        }

        $hitRatio = ($hits / ($iterations - 1)) * 100; // Exclude first iteration
        $averageTime = ($totalTime / $iterations) * 1000; // Convert to milliseconds
        $minHitRatio = $this->performanceThresholds['cache_hit_ratio'][$threshold];

        $this->assertGreaterThan($minHitRatio, $hitRatio,
            "Cache hit ratio should be greater than {$minHitRatio}%, got {$hitRatio}%");

        return [
            'hit_ratio' => $hitRatio,
            'average_time' => $averageTime,
            'total_iterations' => $iterations,
            'cache_hits' => $hits,
        ];
    }

    /**
     * Perform load testing on an endpoint
     *
     * @param string $url URL to test
     * @param int $concurrency Number of concurrent requests
     * @param int $requests Total number of requests
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array Load test metrics
     */
    protected function assertLoadPerformance(string $url, int $concurrency = 5, int $requests = 50, string $method = 'GET', array $data = []): array
    {
        $startTime = microtime(true);
        $responseTimes = [];
        $errors = 0;
        $successfulRequests = 0;

        // Simulate concurrent requests
        for ($i = 0; $i < $requests; $i++) {
            $requestStartTime = microtime(true);

            try {
                switch (strtoupper($method)) {
                    case 'GET':
                        $this->get($url);
                        break;
                    case 'POST':
                        $this->post($url, $data);
                        break;
                }

                if ($this->_response->getStatusCode() < 400) {
                    $successfulRequests++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }

            $requestEndTime = microtime(true);
            $responseTimes[] = ($requestEndTime - $requestStartTime) * 1000;

            // Add small delay to simulate real-world usage
            if ($i % $concurrency === 0) {
                usleep(10000); // 10ms delay every batch
            }
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;
        $averageResponseTime = array_sum($responseTimes) / count($responseTimes);
        $requestsPerSecond = $requests / (($endTime - $startTime));

        $metrics = [
            'total_requests' => $requests,
            'successful_requests' => $successfulRequests,
            'errors' => $errors,
            'error_rate' => ($errors / $requests) * 100,
            'total_time' => $totalTime,
            'average_response_time' => $averageResponseTime,
            'min_response_time' => min($responseTimes),
            'max_response_time' => max($responseTimes),
            'requests_per_second' => $requestsPerSecond,
        ];

        // Assert reasonable error rate
        $this->assertLessThan(10, $metrics['error_rate'], 
            "Error rate should be less than 10%, got {$metrics['error_rate']}%");

        // Assert reasonable average response time
        $this->assertLessThan($this->performanceThresholds['response_time']['slow'], $averageResponseTime,
            "Average response time should be less than {$this->performanceThresholds['response_time']['slow']}ms, got {$averageResponseTime}ms");

        return $metrics;
    }

    /**
     * Test database connection pool performance
     *
     * @return array Connection pool metrics
     */
    protected function assertDatabaseConnectionPerformance(): array
    {
        $connection = ConnectionManager::get('default');
        $iterations = 100;
        $connectionTimes = [];

        for ($i = 0; $i < $iterations; $i++) {
            $startTime = microtime(true);
            
            // Execute a simple query to test connection
            $result = $connection->execute('SELECT 1 as test')->fetchAll();
            
            $endTime = microtime(true);
            $connectionTimes[] = ($endTime - $startTime) * 1000;
        }

        $averageTime = array_sum($connectionTimes) / count($connectionTimes);
        $maxTime = max($connectionTimes);
        $minTime = min($connectionTimes);

        $metrics = [
            'iterations' => $iterations,
            'average_time' => $averageTime,
            'min_time' => $minTime,
            'max_time' => $maxTime,
            'total_time' => array_sum($connectionTimes),
        ];

        // Assert reasonable database connection time
        $this->assertLessThan(50, $averageTime, 
            "Database connection time should be less than 50ms, got {$averageTime}ms");

        return $metrics;
    }

    /**
     * Get current query count from database connection
     *
     * @return int Query count
     */
    protected function getCurrentQueryCount(): int
    {
        $connection = ConnectionManager::get('default');
        $logger = $connection->getLogger();
        
        if (method_exists($logger, 'getLogs')) {
            return count($logger->getLogs());
        }
        
        return 0;
    }

    /**
     * Format bytes into human readable format
     *
     * @param int $bytes Bytes
     * @return string Formatted string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Generate performance report
     *
     * @return array Performance report
     */
    protected function generatePerformanceReport(): array
    {
        $report = [
            'summary' => [
                'total_benchmarks' => count($this->benchmarks),
                'test_thread_id' => $this->getThreadId(),
                'generated_at' => FrozenTime::now()->toISOString(),
            ],
            'benchmarks' => [],
            'recommendations' => [],
        ];

        foreach ($this->benchmarks as $name => $benchmark) {
            if (!isset($benchmark['duration'])) {
                continue;
            }

            $report['benchmarks'][$name] = [
                'duration_ms' => round($benchmark['duration'], 2),
                'memory_used' => $this->formatBytes($benchmark['memory_used']),
                'queries_executed' => $benchmark['queries_executed'],
                'performance_rating' => $this->getRating($benchmark),
            ];

            // Generate recommendations
            if ($benchmark['duration'] > $this->performanceThresholds['response_time']['acceptable']) {
                $report['recommendations'][] = "Consider optimizing {$name} - response time is {$benchmark['duration']}ms";
            }

            if ($benchmark['queries_executed'] > $this->performanceThresholds['queries']['acceptable']) {
                $report['recommendations'][] = "Consider reducing database queries for {$name} - executed {$benchmark['queries_executed']} queries";
            }

            if ($benchmark['memory_used'] > $this->performanceThresholds['memory']['medium']) {
                $report['recommendations'][] = "Consider reducing memory usage for {$name} - used " . $this->formatBytes($benchmark['memory_used']);
            }
        }

        return $report;
    }

    /**
     * Get performance rating for a benchmark
     *
     * @param array $benchmark Benchmark data
     * @return string Performance rating
     */
    protected function getRating(array $benchmark): string
    {
        $score = 0;
        $maxScore = 3;

        // Response time scoring
        if ($benchmark['duration'] <= $this->performanceThresholds['response_time']['fast']) {
            $score += 1;
        } elseif ($benchmark['duration'] <= $this->performanceThresholds['response_time']['acceptable']) {
            $score += 0.5;
        }

        // Query count scoring
        if ($benchmark['queries_executed'] <= $this->performanceThresholds['queries']['few']) {
            $score += 1;
        } elseif ($benchmark['queries_executed'] <= $this->performanceThresholds['queries']['acceptable']) {
            $score += 0.5;
        }

        // Memory usage scoring
        if ($benchmark['memory_used'] <= $this->performanceThresholds['memory']['low']) {
            $score += 1;
        } elseif ($benchmark['memory_used'] <= $this->performanceThresholds['memory']['medium']) {
            $score += 0.5;
        }

        $percentage = ($score / $maxScore) * 100;

        if ($percentage >= 80) {
            return 'Excellent';
        } elseif ($percentage >= 60) {
            return 'Good';
        } elseif ($percentage >= 40) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * Save performance report to file
     *
     * @param array $report Performance report
     * @param string $filename Filename (optional)
     * @return string File path
     */
    protected function savePerformanceReport(array $report, string $filename = null): string
    {
        if ($filename === null) {
            $filename = 'performance_report_' . $this->getThreadId() . '_' . date('Y-m-d_H-i-s') . '.json';
        }

        $reportPath = TMP . 'tests' . DS . $filename;
        
        // Ensure directory exists
        $dir = dirname($reportPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        return $reportPath;
    }
}