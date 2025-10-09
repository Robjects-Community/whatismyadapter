<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\Core\Configure;
use Cake\Http\Session;
use Cake\Utility\Security;

/**
 * WillowControllerTestCase Base Class
 * 
 * Specialized base class for testing controllers in WillowCMS.
 * Provides authentication, session management, and HTTP request testing utilities.
 */
abstract class WillowControllerTestCase extends WillowTestCase
{
    use IntegrationTestTrait;

    /**
     * Current authenticated user
     * @var array|null
     */
    protected $authenticatedUser = null;

    /**
     * CSRF token for forms
     * @var string|null
     */
    protected $csrfToken = null;

    /**
     * Setup method called before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $this->setupControllerTesting();
    }

    /**
     * Setup controller-specific testing environment
     *
     * @return void
     */
    protected function setupControllerTesting(): void
    {
        // Configure session for testing
        $this->configRequest([
            'environment' => [
                'PHP_AUTH_USER' => null,
                'PHP_AUTH_PW' => null,
            ],
        ]);
        
        // Setup CSRF token
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    /**
     * Login a test user for authenticated requests
     *
     * @param string $userType User type (admin, user, inactive)
     * @return array User data
     */
    protected function loginUser(string $userType = 'user'): array
    {
        if (!isset($this->testUsers[$userType])) {
            $this->fail("Unknown user type: {$userType}");
        }

        $userData = $this->testUsers[$userType];
        
        // Create user in database if needed
        $user = $this->createTestData('Users', [
            'email' => $userData['email'],
            'password' => $userData['password'],
            'role' => $userData['role'],
            'active' => $userData['active'],
        ]);

        // Set session authentication
        $this->session([
            'Auth.User' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'active' => $user['active'],
            ]
        ]);

        $this->authenticatedUser = $user;
        
        return $user;
    }

    /**
     * Logout the current user
     *
     * @return void
     */
    protected function logoutUser(): void
    {
        $this->session(['Auth.User' => null]);
        $this->authenticatedUser = null;
    }

    /**
     * Test that a route requires authentication
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $url URL to test
     * @param array $data Request data (for POST/PUT requests)
     * @return void
     */
    protected function assertRequiresAuth(string $method, string $url, array $data = []): void
    {
        // Ensure user is logged out
        $this->logoutUser();
        
        // Make request
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
            default:
                $this->fail("Unsupported HTTP method: {$method}");
        }

        // Should redirect to login or return 401/403
        $this->assertResponseCode([302, 401, 403]);
        
        if ($this->_response->getStatusCode() === 302) {
            $location = $this->_response->getHeaderLine('Location');
            $this->assertStringContainsString('/users/login', $location, 'Should redirect to login page');
        }
    }

    /**
     * Test that a route requires admin role
     *
     * @param string $method HTTP method
     * @param string $url URL to test
     * @param array $data Request data
     * @return void
     */
    protected function assertRequiresAdmin(string $method, string $url, array $data = []): void
    {
        // Test with regular user
        $this->loginUser('user');
        
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

        // Should be forbidden or redirect
        $this->assertResponseCode([302, 403]);
    }

    /**
     * Test successful access with admin user
     *
     * @param string $method HTTP method
     * @param string $url URL to test
     * @param array $data Request data
     * @param int $expectedCode Expected response code
     * @return void
     */
    protected function assertAdminCanAccess(string $method, string $url, array $data = [], int $expectedCode = 200): void
    {
        $this->loginUser('admin');
        
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

        $this->assertResponseCode($expectedCode);
    }

    /**
     * Test CSRF protection on forms
     *
     * @param string $url Form URL
     * @param array $data Form data
     * @return void
     */
    protected function assertCsrfProtected(string $url, array $data = []): void
    {
        $this->loginUser('user');
        
        // Disable CSRF token to test protection
        $this->disableCsrfToken();
        
        $this->post($url, $data);
        $this->assertResponseCode([400, 403]);
        
        // Re-enable for other tests
        $this->enableCsrfToken();
    }

    /**
     * Test rate limiting on an endpoint
     *
     * @param string $url URL to test
     * @param int $maxRequests Maximum allowed requests
     * @param int $timeWindow Time window in seconds
     * @return void
     */
    protected function assertRateLimited(string $url, int $maxRequests = 10, int $timeWindow = 60): void
    {
        // Make requests up to the limit
        for ($i = 0; $i < $maxRequests; $i++) {
            $this->get($url);
            $this->assertResponseCode([200, 302]); // Should work within limit
        }
        
        // Next request should be rate limited
        $this->get($url);
        $this->assertResponseCode(429);
        $this->assertResponseContains('Too Many Requests', (string)$this->_response->getBody());
    }

    /**
     * Assert that response contains validation errors
     *
     * @param array $expectedFields Expected fields with errors
     * @return void
     */
    protected function assertHasValidationErrors(array $expectedFields): void
    {
        $viewVars = $this->viewVariable('entity') ?? $this->viewVariable('article') ?? $this->viewVariable('page');
        
        if ($viewVars && method_exists($viewVars, 'getErrors')) {
            $errors = $viewVars->getErrors();
            
            foreach ($expectedFields as $field) {
                $this->assertArrayHasKey($field, $errors, "Expected validation error for field: {$field}");
            }
        } else {
            // Check flash messages for validation errors
            $flash = $this->viewVariable('flash');
            $this->assertNotNull($flash, 'Expected flash message with validation errors');
        }
    }

    /**
     * Assert that response redirects to success page
     *
     * @param string $expectedUrl Expected redirect URL
     * @param string $expectedMessage Expected flash message
     * @return void
     */
    protected function assertSuccessRedirect(string $expectedUrl, string $expectedMessage = ''): void
    {
        $this->assertRedirect($expectedUrl);
        
        if (!empty($expectedMessage)) {
            $this->assertFlashMessage($expectedMessage);
        }
    }

    /**
     * Assert that a flash message was set
     *
     * @param string $expectedMessage Expected message
     * @param string $type Flash message type (success, error, warning)
     * @return void
     */
    protected function assertFlashMessage(string $expectedMessage, string $type = 'flash'): void
    {
        $flash = $this->viewVariable('flash');
        $this->assertNotNull($flash, 'No flash message found');
        $this->assertStringContainsString($expectedMessage, $flash['message']);
    }

    /**
     * Create and submit a form with data
     *
     * @param string $url Form URL
     * @param array $data Form data
     * @param string $method HTTP method
     * @return void
     */
    protected function submitForm(string $url, array $data, string $method = 'POST'): void
    {
        // Add CSRF token if enabled
        if ($this->csrfToken) {
            $data['_csrfToken'] = $this->csrfToken;
        }

        switch (strtoupper($method)) {
            case 'POST':
                $this->post($url, $data);
                break;
            case 'PUT':
                $this->put($url, $data);
                break;
            case 'PATCH':
                $this->patch($url, $data);
                break;
            default:
                $this->fail("Invalid form method: {$method}");
        }
    }

    /**
     * Test that a controller action renders the correct template
     *
     * @param string $url URL to test
     * @param string $expectedTemplate Expected template name
     * @return void
     */
    protected function assertRendersTemplate(string $url, string $expectedTemplate): void
    {
        $this->get($url);
        $this->assertResponseOk();
        $this->assertTemplate($expectedTemplate);
    }

    /**
     * Test pagination functionality
     *
     * @param string $baseUrl Base URL for pagination
     * @param string $listVariable Variable name for paginated data
     * @return void
     */
    protected function assertPaginationWorks(string $baseUrl, string $listVariable): void
    {
        // Test first page
        $this->get($baseUrl);
        $this->assertResponseOk();
        
        $firstPageData = $this->viewVariable($listVariable);
        $this->assertNotEmpty($firstPageData, "No data found for {$listVariable}");
        
        // Test second page if more data exists
        $this->get($baseUrl . '?page=2');
        $this->assertResponseCode([200, 404]); // 404 if no more pages
        
        // Test invalid page
        $this->get($baseUrl . '?page=999');
        $this->assertResponseCode([200, 404]);
    }

    /**
     * Get the current authenticated user
     *
     * @return array|null User data or null if not authenticated
     */
    protected function getAuthenticatedUser(): ?array
    {
        return $this->authenticatedUser;
    }

    /**
     * Mock an AJAX request
     *
     * @param string $method HTTP method
     * @param string $url URL
     * @param array $data Request data
     * @return void
     */
    protected function ajaxRequest(string $method, string $url, array $data = []): void
    {
        $this->configRequest([
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ]
        ]);

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
    }

    /**
     * Assert that response is valid JSON
     *
     * @return array Decoded JSON data
     */
    protected function assertJsonResponse(): array
    {
        $this->assertContentType('application/json');
        
        $body = (string)$this->_response->getBody();
        $json = json_decode($body, true);
        
        $this->assertNotNull($json, 'Response is not valid JSON');
        
        return $json;
    }
}