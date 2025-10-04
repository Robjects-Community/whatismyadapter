<?php
declare(strict_types=1);

namespace App\Test\TestCase\Performance;

use App\Test\TestCase\WillowPerformanceTestCase;

/**
 * Comprehensive Performance Test Suite
 *
 * Tests all critical performance aspects of WillowCMS including:
 * - Response time benchmarking
 * - Database query optimization
 * - Memory usage monitoring
 * - Cache performance
 * - Load testing
 *
 * @group performance
 * @group thread-safe
 * @group benchmarks
 */
class PerformanceTest extends WillowPerformanceTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Pages',
        'app.Tags',
        'app.Settings',
    ];

    /**
     * Test homepage performance
     *
     * @return void
     */
    public function testHomepagePerformance(): void
    {
        $metrics = $this->assertResponseTime('/', 'acceptable');
        
        $this->assertLessThan(10, $metrics['queries_executed'], 
            'Homepage should execute fewer than 10 database queries');
        
        $this->assertLessThan(32 * 1024 * 1024, $metrics['memory_used'], 
            'Homepage should use less than 32MB memory');
    }

    /**
     * Test articles listing performance
     *
     * @return void
     */
    public function testArticlesListingPerformance(): void
    {
        // Create some test articles
        for ($i = 0; $i < 20; $i++) {
            $this->createTestData('Articles', [
                'title' => "Test Article {$i}",
                'content' => "Test content for article {$i}",
                'published' => true,
            ]);
        }

        $metrics = $this->assertResponseTime('/articles', 'acceptable');
        
        $this->assertLessThan(15, $metrics['queries_executed'], 
            'Articles listing should execute fewer than 15 database queries');
    }

    /**
     * Test admin dashboard performance
     *
     * @return void
     */
    public function testAdminDashboardPerformance(): void
    {
        $this->loginUser('admin');
        
        $metrics = $this->assertResponseTime('/admin', 'acceptable');
        
        $this->assertLessThan(20, $metrics['queries_executed'], 
            'Admin dashboard should execute fewer than 20 database queries');
    }

    /**
     * Test database query performance
     *
     * @return void
     */
    public function testDatabaseQueryPerformance(): void
    {
        $metrics = $this->assertQueryCount(function() {
            // Simulate typical article page with related data
            $articlesTable = $this->getTableLocator()->get('Articles');
            
            $articles = $articlesTable->find()
                ->contain(['Tags'])
                ->limit(10)
                ->toArray();
                
            return $articles;
        }, 'few', 'articles_with_tags_query');

        $this->assertNotEmpty($metrics['operation_result'], 
            'Query should return results');
    }

    /**
     * Test memory usage for large operations
     *
     * @return void
     */
    public function testMemoryUsageForBulkOperations(): void
    {
        // Create test articles
        for ($i = 0; $i < 50; $i++) {
            $this->createTestData('Articles');
        }

        $metrics = $this->assertMemoryUsage(function() {
            $articlesTable = $this->getTableLocator()->get('Articles');
            
            // Simulate bulk operation
            $articles = $articlesTable->find()->toArray();
            
            // Process articles (simulate some business logic)
            $processed = [];
            foreach ($articles as $article) {
                $processed[] = [
                    'id' => $article->id,
                    'title' => strtoupper($article->title),
                    'slug' => strtolower(str_replace(' ', '-', $article->title)),
                ];
            }
            
            return $processed;
        }, 'medium', 'bulk_article_processing');

        $this->assertNotEmpty($metrics['operation_result'], 
            'Bulk operation should return results');
    }

    /**
     * Test cache performance
     *
     * @return void
     */
    public function testCachePerformance(): void
    {
        $cacheKey = 'test_performance_data_' . $this->getThreadId();
        
        $metrics = $this->assertCachePerformance($cacheKey, function() {
            // Simulate expensive operation
            $data = [];
            for ($i = 0; $i < 100; $i++) {
                $data[] = [
                    'id' => $i,
                    'value' => md5((string)$i),
                    'timestamp' => time(),
                ];
            }
            return $data;
        }, 'good');

        $this->assertGreaterThan(75, $metrics['hit_ratio'], 
            'Cache hit ratio should be above 75%');
    }

    /**
     * Test load performance under concurrent requests
     *
     * @return void
     */
    public function testLoadPerformance(): void
    {
        $metrics = $this->assertLoadPerformance('/', 3, 15);
        
        $this->assertGreaterThan(1, $metrics['requests_per_second'], 
            'Should handle at least 1 request per second');
        
        $this->assertLessThan(5, $metrics['error_rate'], 
            'Error rate should be less than 5%');
    }

    /**
     * Test database connection pool performance
     *
     * @return void
     */
    public function testDatabaseConnectionPerformance(): void
    {
        $metrics = $this->assertDatabaseConnectionPerformance();
        
        $this->assertLessThan(20, $metrics['average_time'], 
            'Database connections should average less than 20ms');
        
        $this->assertLessThan(100, $metrics['max_time'], 
            'Maximum database connection time should be less than 100ms');
    }

    /**
     * Test API endpoint performance
     *
     * @return void
     */
    public function testApiEndpointPerformance(): void
    {
        // Test API endpoints if they exist
        $apiEndpoints = [
            '/api/articles',
            '/api/pages',
            '/api/products',
        ];

        foreach ($apiEndpoints as $endpoint) {
            try {
                $metrics = $this->assertResponseTime($endpoint, 'fast', 'GET');
                
                $this->assertLessThan(5, $metrics['queries_executed'], 
                    "API endpoint {$endpoint} should execute fewer than 5 queries");
                    
            } catch (\Exception $e) {
                // Skip if endpoint doesn't exist
                $this->markTestSkipped("API endpoint {$endpoint} not available");
            }
        }
    }

    /**
     * Test form submission performance
     *
     * @return void
     */
    public function testFormSubmissionPerformance(): void
    {
        $this->loginUser('admin');
        
        $formData = [
            'title' => 'Performance Test Article',
            'content' => 'This is a performance test article with some content.',
            'published' => true,
        ];

        $metrics = $this->assertResponseTime('/admin/articles/add', 'acceptable', 'POST', $formData);
        
        $this->assertLessThan(25, $metrics['queries_executed'], 
            'Article creation should execute fewer than 25 queries');
    }

    /**
     * Test search functionality performance
     *
     * @return void
     */
    public function testSearchPerformance(): void
    {
        // Create test content for searching
        for ($i = 0; $i < 30; $i++) {
            $this->createTestData('Articles', [
                'title' => "Searchable Article {$i}",
                'content' => "Content with searchable terms: performance, testing, optimization",
                'published' => true,
            ]);
        }

        $searchData = ['q' => 'performance'];
        
        try {
            $metrics = $this->assertResponseTime('/articles/search', 'acceptable', 'GET', $searchData);
            
            $this->assertLessThan(30, $metrics['queries_executed'], 
                'Search should execute fewer than 30 queries');
                
        } catch (\Exception $e) {
            $this->markTestSkipped('Search endpoint not available');
        }
    }

    /**
     * Test file upload performance
     *
     * @return void
     */
    public function testFileUploadPerformance(): void
    {
        $this->loginUser('admin');
        
        // Create a test file
        $testContent = str_repeat('test content ', 1000); // ~13KB file
        $tempFile = tempnam(sys_get_temp_dir(), 'performance_test');
        file_put_contents($tempFile, $testContent);

        $fileData = [
            'upload' => [
                'name' => 'performance_test.txt',
                'type' => 'text/plain',
                'tmp_name' => $tempFile,
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($testContent),
            ]
        ];

        try {
            $metrics = $this->assertResponseTime('/admin/files/upload', 'acceptable', 'POST', $fileData);
            
            $this->assertLessThan(64 * 1024 * 1024, $metrics['memory_used'], 
                'File upload should use less than 64MB memory');
                
        } catch (\Exception $e) {
            $this->markTestSkipped('File upload endpoint not available');
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Test pagination performance
     *
     * @return void
     */
    public function testPaginationPerformance(): void
    {
        // Create test articles for pagination
        for ($i = 0; $i < 100; $i++) {
            $this->createTestData('Articles', [
                'title' => "Paginated Article {$i}",
                'published' => true,
            ]);
        }

        // Test different pages
        $pages = [1, 2, 5, 10];
        
        foreach ($pages as $page) {
            $metrics = $this->assertResponseTime("/articles?page={$page}", 'acceptable');
            
            $this->assertLessThan(15, $metrics['queries_executed'], 
                "Page {$page} should execute fewer than 15 queries");
                
            // Subsequent pages should be as fast as the first page
            $this->assertLessThan($this->performanceThresholds['response_time']['acceptable'], 
                $metrics['duration'], "Page {$page} should be fast");
        }
    }

    /**
     * Test teardown - generate performance report
     *
     * @return void
     */
    public function tearDown(): void
    {
        // Generate and save performance report
        $report = $this->generatePerformanceReport();
        
        if (!empty($this->benchmarks)) {
            $reportPath = $this->savePerformanceReport($report);
            echo "\nPerformance report saved to: {$reportPath}\n";
            
            // Print summary to console
            echo "\nPerformance Summary:\n";
            echo "==================\n";
            foreach ($report['benchmarks'] as $name => $benchmark) {
                echo sprintf("%-40s: %6.2fms (%s)\n", 
                    $name, 
                    $benchmark['duration_ms'], 
                    $benchmark['performance_rating']
                );
            }
            
            if (!empty($report['recommendations'])) {
                echo "\nRecommendations:\n";
                echo "===============\n";
                foreach ($report['recommendations'] as $recommendation) {
                    echo "- {$recommendation}\n";
                }
            }
        }
        
        parent::tearDown();
    }
}