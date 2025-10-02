<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Http\Session;
use Cake\Controller\ComponentRegistry;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;

/**
 * WillowTestCase Base Class
 * 
 * Provides common functionality and utilities for all WillowCMS tests.
 * This class ensures consistent testing practices across all MVC components
 * and provides thread-safe testing utilities.
 */
abstract class WillowTestCase extends TestCase
{
    /**
     * Thread-specific test data
     * @var array
     */
    protected $threadData = [];
    
    /**
     * Test user data for authentication tests
     * @var array
     */
    protected $testUsers = [];
    
    /**
     * Mock services registry
     * @var array
     */
    protected $mockServices = [];

    /**
     * Setup method called before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $this->setupThreadIsolation();
        $this->setupTestEnvironment();
        $this->setupMockServices();
        $this->setupTestUsers();
        
        // Freeze time for consistent testing
        FrozenTime::setTestNow('2024-01-15 10:00:00');
    }

    /**
     * Teardown method called after each test
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->cleanupThreadData();
        $this->cleanupMockServices();
        
        // Unfreeze time
        FrozenTime::setTestNow(null);
        
        parent::tearDown();
    }

    /**
     * Setup thread isolation for parallel testing
     *
     * @return void
     */
    protected function setupThreadIsolation(): void
    {
        $threadId = getenv('THREAD_ID') ?: 'default';
        
        // Configure thread-specific cache prefix
        Cache::setConfig('test', [
            'className' => 'File',
            'path' => TMP . 'cache' . DS,
            'prefix' => 'willow_test_' . $threadId . '_',
            'duration' => '+1 hour',
        ]);
        
        // Store thread ID for use in tests
        $this->threadData['id'] = $threadId;
        $this->threadData['cache_prefix'] = 'willow_test_' . $threadId . '_';
    }

    /**
     * Setup test environment configuration
     *
     * @return void
     */
    protected function setupTestEnvironment(): void
    {
        // Disable debug mode for consistent testing
        Configure::write('debug', false);
        
        // Setup test-specific configurations
        Configure::write('App.fullBaseUrl', 'http://localhost');
        Configure::write('Session.defaults', 'php');
        
        // Disable CSRF for API tests
        Configure::write('Security.csrfUseOnce', false);
        
        // Setup test email configuration
        Configure::write('EmailTransport.test', [
            'className' => 'Debug'
        ]);
        
        // Configure test logging
        Configure::write('Log.test', [
            'className' => 'Array',
            'levels' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
        ]);
    }

    /**
     * Setup mock services for consistent testing
     *
     * @return void
     */
    protected function setupMockServices(): void
    {
        // Mock AI services to avoid API calls during tests
        $this->mockServices['anthropic'] = $this->createMock(\App\Service\Api\Anthropic\AnthropicApiService::class);
        $this->mockServices['google'] = $this->createMock(\App\Service\Api\Google\GoogleTranslateService::class);
        
        // Mock external HTTP requests
        $this->mockServices['http'] = $this->createMock(\Cake\Http\Client::class);
    }

    /**
     * Setup test user data
     *
     * @return void
     */
    protected function setupTestUsers(): void
    {
        $this->testUsers = [
            'admin' => [
                'email' => 'admin@test.com',
                'password' => 'testpassword123',
                'role' => 'admin',
                'active' => true,
            ],
            'user' => [
                'email' => 'user@test.com', 
                'password' => 'userpassword123',
                'role' => 'user',
                'active' => true,
            ],
            'inactive' => [
                'email' => 'inactive@test.com',
                'password' => 'password123',
                'role' => 'user',
                'active' => false,
            ]
        ];
    }

    /**
     * Cleanup thread-specific data
     *
     * @return void
     */
    protected function cleanupThreadData(): void
    {
        // Clear thread-specific cache
        if (isset($this->threadData['cache_prefix'])) {
            Cache::clear('test');
        }
        
        $this->threadData = [];
    }

    /**
     * Cleanup mock services
     *
     * @return void
     */
    protected function cleanupMockServices(): void
    {
        $this->mockServices = [];
    }

    /**
     * Get a mock service by name
     *
     * @param string $serviceName Service name
     * @return mixed Mock service or null
     */
    protected function getMockService(string $serviceName)
    {
        return $this->mockServices[$serviceName] ?? null;
    }

    /**
     * Create a test user session
     *
     * @param string $userType User type (admin, user, inactive)
     * @return Session Test session
     */
    protected function createTestSession(string $userType = 'user'): Session
    {
        $session = new Session();
        
        if (isset($this->testUsers[$userType])) {
            $userData = $this->testUsers[$userType];
            $session->write('Auth.User', [
                'id' => 1,
                'email' => $userData['email'],
                'role' => $userData['role'],
                'active' => $userData['active'],
            ]);
        }
        
        return $session;
    }

    /**
     * Assert that a redirect occurred to a specific URL
     *
     * @param string $expectedUrl Expected redirect URL
     * @param array $response Response array from controller test
     * @return void
     */
    protected function assertRedirectTo(string $expectedUrl, array $response): void
    {
        $this->assertArrayHasKey('status', $response);
        $this->assertTrue(in_array($response['status'], [301, 302, 303, 307, 308]));
        $this->assertArrayHasKey('headers', $response);
        $this->assertArrayHasKey('Location', $response['headers']);
        $this->assertStringContainsString($expectedUrl, $response['headers']['Location'][0]);
    }

    /**
     * Assert that response contains specific content
     *
     * @param string $expectedContent Expected content
     * @param string $actualBody Response body
     * @return void
     */
    protected function assertResponseContains(string $expectedContent, string $actualBody): void
    {
        $this->assertStringContainsString($expectedContent, $actualBody);
    }

    /**
     * Assert that response does not contain specific content
     *
     * @param string $forbiddenContent Forbidden content
     * @param string $actualBody Response body
     * @return void
     */
    protected function assertResponseNotContains(string $forbiddenContent, string $actualBody): void
    {
        $this->assertStringNotContainsString($forbiddenContent, $actualBody);
    }

    /**
     * Create test data for a specific table
     *
     * @param string $tableName Table name
     * @param array $data Test data
     * @return array Created entity data
     */
    protected function createTestData(string $tableName, array $data = []): array
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        
        // Apply default test data based on table
        $defaults = $this->getDefaultTestData($tableName);
        $data = array_merge($defaults, $data);
        
        $entity = $table->newEntity($data);
        $result = $table->save($entity);
        
        $this->assertNotFalse($result, "Failed to create test data for {$tableName}");
        
        return $result->toArray();
    }

    /**
     * Get default test data for a table
     *
     * @param string $tableName Table name
     * @return array Default test data
     */
    protected function getDefaultTestData(string $tableName): array
    {
        $defaults = [
            'Articles' => [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test article content',
                'published' => true,
                'created' => FrozenTime::now(),
                'modified' => FrozenTime::now(),
            ],
            'Pages' => [
                'title' => 'Test Page',
                'slug' => 'test-page',
                'content' => 'Test page content',
                'published' => true,
                'created' => FrozenTime::now(),
                'modified' => FrozenTime::now(),
            ],
            'Users' => [
                'email' => 'test' . uniqid() . '@example.com',
                'password' => Security::hash('password123'),
                'role' => 'user',
                'active' => true,
                'created' => FrozenTime::now(),
                'modified' => FrozenTime::now(),
            ],
            'Tags' => [
                'name' => 'Test Tag ' . uniqid(),
                'slug' => 'test-tag-' . uniqid(),
                'created' => FrozenTime::now(),
                'modified' => FrozenTime::now(),
            ]
        ];

        return $defaults[$tableName] ?? [];
    }

    /**
     * Mock an AI service response
     *
     * @param string $service Service name (anthropic, google)
     * @param string $method Method name
     * @param mixed $returnValue Return value
     * @return void
     */
    protected function mockAiService(string $service, string $method, $returnValue): void
    {
        if (isset($this->mockServices[$service])) {
            $this->mockServices[$service]
                ->expects($this->any())
                ->method($method)
                ->willReturn($returnValue);
        }
    }

    /**
     * Get thread-specific test identifier
     *
     * @return string Thread-specific identifier
     */
    protected function getThreadId(): string
    {
        return $this->threadData['id'];
    }

    /**
     * Assert that a table has a specific number of records
     *
     * @param string $tableName Table name
     * @param int $expectedCount Expected record count
     * @return void
     */
    protected function assertTableRecordCount(string $tableName, int $expectedCount): void
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $actualCount = $table->find()->count();
        
        $this->assertEquals(
            $expectedCount, 
            $actualCount, 
            "Table {$tableName} should have {$expectedCount} records, but has {$actualCount}"
        );
    }

    /**
     * Assert that a validation error occurred for a specific field
     *
     * @param array $errors Validation errors
     * @param string $field Field name
     * @param string|null $expectedMessage Expected error message (optional)
     * @return void
     */
    protected function assertValidationError(array $errors, string $field, ?string $expectedMessage = null): void
    {
        $this->assertArrayHasKey($field, $errors, "No validation error found for field: {$field}");
        
        if ($expectedMessage !== null) {
            $fieldErrors = $errors[$field];
            $found = false;
            
            foreach ($fieldErrors as $error) {
                if (strpos($error, $expectedMessage) !== false) {
                    $found = true;
                    break;
                }
            }
            
            $this->assertTrue($found, "Expected validation message '{$expectedMessage}' not found for field {$field}");
        }
    }
}