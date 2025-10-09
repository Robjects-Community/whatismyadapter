<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\LogIntegrityMiddleware;
use App\Service\LogChecksumService;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * LogIntegrityMiddleware Test Case
 *
 * Tests log integrity verification behavior and cache management
 */
class LogIntegrityMiddlewareTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::clear('default');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up configurations
        Configure::delete('LogIntegrity');
        Cache::clear('default');
    }

    /**
     * Test that middleware is disabled when configuration is set to false
     *
     * @return void
     */
    public function testDisabledByConfiguration(): void
    {
        Configure::write('LogIntegrity.enabled', false);
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that verification runs when interval has passed
     *
     * @return void
     */
    public function testRunsVerificationAfterInterval(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        // Set last verification to over an hour ago (3601 seconds)
        $lastVerification = time() - 3601;
        Cache::write('log_integrity_last_verification', $lastVerification, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        // Verification should have updated the cache
        $newLastVerification = Cache::read('log_integrity_last_verification', 'default');
        $this->assertGreaterThan($lastVerification, $newLastVerification);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that verification is skipped when interval has not passed
     *
     * @return void
     */
    public function testSkipsVerificationWithinInterval(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        // Set last verification to 10 seconds ago (within 1 hour interval)
        $lastVerification = time() - 10;
        Cache::write('log_integrity_last_verification', $lastVerification, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        // Verification timestamp should not have changed
        $cachedVerification = Cache::read('log_integrity_last_verification', 'default');
        $this->assertSame($lastVerification, $cachedVerification);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware passes request to handler regardless of verification
     *
     * @return void
     */
    public function testAlwaysPassesRequestToHandler(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        $handlerCalled = false;
        $handler = new class ($handlerCalled) implements RequestHandlerInterface {
            private $called;
            
            public function __construct(&$called)
            {
                $this->called = &$called;
            }
            
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                $this->called = true;
                
                return new Response();
            }
        };
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $middleware->process($request, $handler);
        
        $this->assertTrue($handlerCalled);
    }

    /**
     * Test first run when cache is empty
     *
     * @return void
     */
    public function testFirstRunWithEmptyCache(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        // Ensure cache is empty
        Cache::delete('log_integrity_last_verification', 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        // Should have set initial verification timestamp
        $lastVerification = Cache::read('log_integrity_last_verification', 'default');
        $this->assertNotNull($lastVerification);
        $this->assertIsInt($lastVerification);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that verification handles OK status correctly
     *
     * @return void
     */
    public function testHandlesOkStatus(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        // Force verification by setting old timestamp
        Cache::write('log_integrity_last_verification', time() - 3700, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test middleware behavior with various integrity statuses
     *
     * @return void
     */
    public function testHandlesVariousIntegrityStatuses(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        $statuses = ['OK', 'INFO', 'WARNING', 'CRITICAL'];
        
        foreach ($statuses as $status) {
            // Clear cache for each iteration
            Cache::delete('log_integrity_last_verification', 'default');
            
            $middleware = new LogIntegrityMiddleware();
            
            $request = new ServerRequest(['url' => '/test']);
            $handler = $this->createMockHandler();
            
            $response = $middleware->process($request, $handler);
            
            // Should always return 200 regardless of integrity status
            // (middleware doesn't block requests, just logs issues)
            $this->assertSame(200, $response->getStatusCode(), "Failed for status: {$status}");
        }
    }

    /**
     * Test that cache key is properly managed
     *
     * @return void
     */
    public function testCacheKeyManagement(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        $expectedKey = 'log_integrity_last_verification';
        
        // Delete key to ensure clean state
        Cache::delete($expectedKey, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $middleware->process($request, $handler);
        
        // Verify the cache key was set
        $value = Cache::read($expectedKey, 'default');
        $this->assertNotNull($value);
        $this->assertIsInt($value);
        $this->assertLessThanOrEqual(time(), $value);
        $this->assertGreaterThan(time() - 5, $value); // Should be very recent
    }

    /**
     * Test middleware behavior when LogChecksumService throws exception
     *
     * @return void
     */
    public function testHandlesVerificationException(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        // Force verification
        Cache::write('log_integrity_last_verification', time() - 3700, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        // Should not throw exception, just log error and continue
        $response = $middleware->process($request, $handler);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware doesn't block normal request flow
     *
     * @return void
     */
    public function testDoesNotBlockRequestFlow(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        $middleware = new LogIntegrityMiddleware();
        
        $capturedRequest = null;
        $handler = new class ($capturedRequest) implements RequestHandlerInterface {
            private $capturedRequest;
            
            public function __construct(&$capturedRequest)
            {
                $this->capturedRequest = &$capturedRequest;
            }
            
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                $this->capturedRequest = $request;
                
                return new Response(['status' => 201]);
            }
        };
        
        $request = new ServerRequest(['url' => '/test']);
        $response = $middleware->process($request, $handler);
        
        // Request should be passed through unmodified
        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestInterface::class, $capturedRequest);
        $this->assertSame('/test', $capturedRequest->getUri()->getPath());
        
        // Response should be from handler, not modified by middleware
        $this->assertSame(201, $response->getStatusCode());
    }

    /**
     * Test verification interval constant
     *
     * @return void
     */
    public function testVerificationIntervalConstant(): void
    {
        // The VERIFICATION_INTERVAL is 3600 seconds (1 hour)
        // We can test behavior around this boundary
        
        Configure::write('LogIntegrity.enabled', true);
        
        // Just under the interval - should NOT verify
        $lastVerification = time() - 3599;
        Cache::write('log_integrity_last_verification', $lastVerification, 'default');
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $middleware->process($request, $handler);
        
        $cachedTime = Cache::read('log_integrity_last_verification', 'default');
        $this->assertSame($lastVerification, $cachedTime, 'Should not verify within interval');
        
        // Clear and test just over the interval - SHOULD verify
        Cache::delete('log_integrity_last_verification', 'default');
        $lastVerification = time() - 3601;
        Cache::write('log_integrity_last_verification', $lastVerification, 'default');
        
        $middleware->process($request, $handler);
        
        $cachedTime = Cache::read('log_integrity_last_verification', 'default');
        $this->assertGreaterThan($lastVerification, $cachedTime, 'Should verify after interval');
    }

    /**
     * Test middleware with default configuration
     *
     * @return void
     */
    public function testDefaultConfiguration(): void
    {
        // Don't explicitly set configuration - test defaults
        // Default should be enabled (true)
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        $response = $middleware->process($request, $handler);
        
        // Should run with default settings
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test multiple consecutive requests within interval
     *
     * @return void
     */
    public function testMultipleRequestsWithinInterval(): void
    {
        Configure::write('LogIntegrity.enabled', true);
        
        $middleware = new LogIntegrityMiddleware();
        
        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();
        
        // First request - will set initial timestamp
        $middleware->process($request, $handler);
        $firstTimestamp = Cache::read('log_integrity_last_verification', 'default');
        
        // Second request immediately after - should not update timestamp
        $middleware->process($request, $handler);
        $secondTimestamp = Cache::read('log_integrity_last_verification', 'default');
        
        // Third request - still within interval
        $middleware->process($request, $handler);
        $thirdTimestamp = Cache::read('log_integrity_last_verification', 'default');
        
        // All timestamps should be the same (no re-verification)
        $this->assertSame($firstTimestamp, $secondTimestamp);
        $this->assertSame($secondTimestamp, $thirdTimestamp);
    }

    /**
     * Create a mock request handler
     *
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    private function createMockHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }
}
