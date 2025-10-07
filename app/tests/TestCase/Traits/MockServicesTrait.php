<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Cache\Cache;
use Cake\Http\Client;
use Cake\Http\Client\Response as HttpResponse;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * MockServicesTrait
 *
 * Provides reusable mocking utilities for external services including:
 * - AI services (Anthropic, Google)
 * - Queue system
 * - Cache layer
 * - Filesystem operations
 * - HTTP clients
 *
 * Usage in test classes:
 * ```php
 * use App\Test\TestCase\Traits\MockServicesTrait;
 *
 * class MyTest extends TestCase
 * {
 *     use MockServicesTrait;
 *
 *     public function testSomething(): void
 *     {
 *         $mockAi = $this->mockAnthropicService(['response' => 'test']);
 *         // ... test code
 *     }
 * }
 * ```
 */
trait MockServicesTrait
{
    // ============================================================
    // AI Service Mocking
    // ============================================================

    /**
     * Create a mock Anthropic AI service
     *
     * @param array $responses Array of responses to return sequentially
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockAnthropicService(array $responses = []): MockObject
    {
        $mock = $this->createMock(\stdClass::class);
        
        if (!empty($responses)) {
            $mock->method('sendMessage')
                ->willReturnOnConsecutiveCalls(...$responses);
        }
        
        return $mock;
    }

    /**
     * Create a mock Google AI service (Translate, etc.)
     *
     * @param array $config Configuration for mock responses
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockGoogleService(array $config = []): MockObject
    {
        $mock = $this->createMock(\stdClass::class);
        
        if (isset($config['translate'])) {
            $mock->method('translate')
                ->willReturn($config['translate']);
        }
        
        if (isset($config['detectLanguage'])) {
            $mock->method('detectLanguage')
                ->willReturn($config['detectLanguage']);
        }
        
        return $mock;
    }

    /**
     * Mock AI metrics recording
     *
     * Prevents actual database writes during tests
     *
     * @return void
     */
    protected function mockAiMetricsRecording(): void
    {
        // Create a spy for the AiMetrics table
        if (method_exists($this, 'getTableLocator')) {
            $metricsTable = $this->getTableLocator()->get('AiMetrics');
            
            // Override save to prevent actual DB writes
            $metricsTable->setEventManager($this->createMock(\Cake\Event\EventManager::class));
        }
    }

    /**
     * Create canned HTTP responses for AI API calls
     *
     * @param string $service Service name (anthropic, google, openai)
     * @param array $responses Array of response data
     * @return \Cake\Http\Client\Response[]
     */
    protected function createAiApiResponses(string $service, array $responses): array
    {
        $httpResponses = [];
        
        foreach ($responses as $response) {
            $body = json_encode($response);
            $httpResponses[] = new HttpResponse([
                'HTTP/1.1 200 OK',
                'Content-Type: application/json',
            ], $body);
        }
        
        return $httpResponses;
    }

    // ============================================================
    // Queue System Mocking
    // ============================================================

    /**
     * Mock queue job execution
     *
     * Jobs will be executed synchronously in tests
     *
     * @return void
     */
    protected function mockQueueSynchronousExecution(): void
    {
        // Configure queue to run synchronously
        if (class_exists('\Cake\Queue\QueueManager')) {
            \Cake\Core\Configure::write('Queue.default.engine', 'Synchronous');
        }
    }

    /**
     * Create a spy to track queued jobs without executing them
     *
     * @return array Reference to array that will collect job calls
     */
    protected function spyQueuedJobs(): array
    {
        $queuedJobs = [];
        
        // Store reference for later assertions
        $this->queuedJobsSpy = &$queuedJobs;
        
        return $queuedJobs;
    }

    /**
     * Assert that a specific job was queued
     *
     * @param string $jobClass Expected job class name
     * @param array $expectedData Expected job data (optional)
     * @return void
     */
    protected function assertJobWasQueued(string $jobClass, array $expectedData = []): void
    {
        $found = false;
        
        foreach ($this->queuedJobsSpy ?? [] as $job) {
            if ($job['class'] === $jobClass) {
                $found = true;
                
                if (!empty($expectedData)) {
                    $this->assertEquals($expectedData, $job['data'], 
                        "Job {$jobClass} was queued but with different data");
                }
                break;
            }
        }
        
        $this->assertTrue($found, "Job {$jobClass} was not queued");
    }

    // ============================================================
    // Cache Mocking
    // ============================================================

    /**
     * Configure cache to use array engine for tests
     *
     * @param string $config Cache configuration name
     * @return void
     */
    protected function useMockCache(string $config = 'default'): void
    {
        Cache::setConfig($config, [
            'className' => 'Array',
            'prefix' => 'test_',
        ]);
    }

    /**
     * Clear all cache for a specific configuration
     *
     * @param string $config Cache configuration name
     * @return void
     */
    protected function clearTestCache(string $config = 'default'): void
    {
        Cache::clear($config);
    }

    /**
     * Seed cache with test data
     *
     * @param array $data Key-value pairs to seed
     * @param string $config Cache configuration name
     * @return void
     */
    protected function seedCache(array $data, string $config = 'default'): void
    {
        foreach ($data as $key => $value) {
            Cache::write($key, $value, $config);
        }
    }

    /**
     * Assert that a value exists in cache
     *
     * @param string $key Cache key
     * @param string $config Cache configuration name
     * @return void
     */
    protected function assertCacheHas(string $key, string $config = 'default'): void
    {
        $exists = Cache::read($key, $config) !== null;
        $this->assertTrue($exists, "Cache key '{$key}' does not exist");
    }

    /**
     * Assert that cache is empty
     *
     * @param string $config Cache configuration name
     * @return void
     */
    protected function assertCacheEmpty(string $config = 'default'): void
    {
        Cache::clear($config);
        // After clearing, verify it's empty by trying to read a known key
        $this->assertNull(Cache::read('__test_cache_empty__', $config));
    }

    // ============================================================
    // Filesystem Mocking
    // ============================================================

    /**
     * Create a mock filesystem adapter
     *
     * Prevents actual file operations during tests
     *
     * @param array $existingFiles Files that should "exist"
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockFilesystem(array $existingFiles = []): MockObject
    {
        $mock = $this->createMock(\League\Flysystem\Filesystem::class);
        
        // Mock has() to return true for existing files
        $mock->method('has')
            ->willReturnCallback(function ($path) use ($existingFiles) {
                return in_array($path, $existingFiles);
            });
        
        // Mock write to always succeed
        $mock->method('write')
            ->willReturn(true);
        
        // Mock read to return file content
        $mock->method('read')
            ->willReturnCallback(function ($path) use ($existingFiles) {
                if (in_array($path, $existingFiles)) {
                    return "mock content for {$path}";
                }
                throw new \Exception("File not found: {$path}");
            });
        
        // Mock delete to always succeed
        $mock->method('delete')
            ->willReturn(true);
        
        return $mock;
    }

    /**
     * Create a temporary test directory
     *
     * Automatically cleaned up after test
     *
     * @return string Path to temporary directory
     */
    protected function createTempTestDir(): string
    {
        $tmpDir = sys_get_temp_dir() . '/willow_test_' . uniqid();
        mkdir($tmpDir, 0777, true);
        
        // Register for cleanup
        $this->tempTestDirs[] = $tmpDir;
        
        return $tmpDir;
    }

    /**
     * Clean up temporary test directories
     *
     * Call this in tearDown() method
     *
     * @return void
     */
    protected function cleanupTempTestDirs(): void
    {
        foreach ($this->tempTestDirs ?? [] as $dir) {
            if (is_dir($dir)) {
                $this->recursiveRemoveDirectory($dir);
            }
        }
        
        $this->tempTestDirs = [];
    }

    /**
     * Recursively remove a directory
     *
     * @param string $dir Directory path
     * @return void
     */
    private function recursiveRemoveDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveRemoveDirectory($path) : unlink($path);
        }
        
        rmdir($dir);
    }

    // ============================================================
    // HTTP Client Mocking
    // ============================================================

    /**
     * Create an HTTP client with canned responses
     *
     * @param array $responses Array of HttpResponse objects
     * @return \Cake\Http\Client
     */
    protected function createMockHttpClient(array $responses): Client
    {
        $adapter = $this->createMock(\Cake\Http\Client\Adapter\AdapterInterface::class);
        
        if (count($responses) === 1) {
            $adapter->method('send')
                ->willReturn($responses[0]);
        } else {
            $adapter->method('send')
                ->willReturnOnConsecutiveCalls(...$responses);
        }
        
        return new Client(['adapter' => $adapter]);
    }

    /**
     * Create a successful HTTP response
     *
     * @param array $data Response data
     * @param int $status HTTP status code
     * @return \Cake\Http\Client\Response
     */
    protected function createSuccessResponse(array $data = [], int $status = 200): HttpResponse
    {
        $body = json_encode($data);
        
        return new HttpResponse([
            "HTTP/1.1 {$status} OK",
            'Content-Type: application/json',
        ], $body);
    }

    /**
     * Create an error HTTP response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return \Cake\Http\Client\Response
     */
    protected function createErrorResponse(string $message = 'Error', int $status = 500): HttpResponse
    {
        $body = json_encode(['error' => $message]);
        
        return new HttpResponse([
            "HTTP/1.1 {$status} Error",
            'Content-Type: application/json',
        ], $body);
    }

    // ============================================================
    // Time Mocking
    // ============================================================

    /**
     * Freeze time for testing
     *
     * @param string|\Cake\I18n\DateTime $time Time to freeze at
     * @return void
     */
    protected function freezeTime($time = 'now'): void
    {
        if (is_string($time)) {
            $time = new DateTime($time);
        }
        
        DateTime::setTestNow($time);
    }

    /**
     * Unfreeze time
     *
     * @return void
     */
    protected function unfreezeTime(): void
    {
        DateTime::setTestNow(null);
    }

    /**
     * Travel forward in time
     *
     * @param string $interval DateInterval string (e.g., '+1 day')
     * @return void
     */
    protected function travelTo(string $interval): void
    {
        $current = DateTime::getTestNow() ?? new DateTime();
        $new = $current->modify($interval);
        
        DateTime::setTestNow($new);
    }

    // ============================================================
    // Email Mocking
    // ============================================================

    /**
     * Configure email transport for testing
     *
     * Emails will be captured but not sent
     *
     * @return array Reference to array that will collect sent emails
     */
    protected function mockEmailTransport(): array
    {
        $sentEmails = [];
        
        \Cake\Mailer\TransportFactory::drop('default');
        \Cake\Mailer\TransportFactory::setConfig('default', [
            'className' => 'Debug',
        ]);
        
        $this->sentEmailsSpy = &$sentEmails;
        
        return $sentEmails;
    }

    /**
     * Assert that an email was sent
     *
     * @param string $to Expected recipient email
     * @param string $subject Expected subject (optional)
     * @return void
     */
    protected function assertEmailWasSent(string $to, ?string $subject = null): void
    {
        $found = false;
        
        foreach ($this->sentEmailsSpy ?? [] as $email) {
            if (in_array($to, $email['to'] ?? [])) {
                $found = true;
                
                if ($subject !== null) {
                    $this->assertEquals($subject, $email['subject'], 
                        "Email to {$to} was sent but with different subject");
                }
                break;
            }
        }
        
        $this->assertTrue($found, "Email to {$to} was not sent");
    }

    // ============================================================
    // Properties
    // ============================================================

    /**
     * @var array Temporary directories created during tests
     */
    private array $tempTestDirs = [];

    /**
     * @var array Spy for queued jobs
     */
    private array $queuedJobsSpy = [];

    /**
     * @var array Spy for sent emails
     */
    private array $sentEmailsSpy = [];
}
