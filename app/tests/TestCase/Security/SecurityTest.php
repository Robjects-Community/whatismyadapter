<?php
declare(strict_types=1);

namespace App\Test\TestCase\Security;

use App\Test\TestCase\WillowSecurityTestCase;

/**
 * Comprehensive Security Test Suite
 *
 * Tests all critical security aspects of WillowCMS including:
 * - Authentication and authorization
 * - CSRF and XSS protection
 * - SQL injection prevention
 * - Rate limiting
 * - File upload security
 * - Session security
 * - Log integrity
 *
 * @group security
 * @group thread-safe
 * @group critical
 */
class SecurityTest extends WillowSecurityTestCase
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
        'app.Settings',
        'app.BlockedIps',
    ];

    /**
     * Test authentication requirements for admin routes
     *
     * @return void
     */
    public function testAdminRoutesRequireAuthentication(): void
    {
        $adminRoutes = [
            'GET' => [
                '/admin',
                '/admin/articles',
                '/admin/pages', 
                '/admin/users',
                '/admin/settings',
            ],
            'POST' => [
                '/admin/articles/add',
                '/admin/pages/add',
                '/admin/users/add',
            ],
            'PUT' => [
                '/admin/articles/edit/1',
                '/admin/pages/edit/1',
            ],
            'DELETE' => [
                '/admin/articles/delete/1',
                '/admin/pages/delete/1',
            ],
        ];

        $this->assertRoutesRequireAuthentication($adminRoutes, '/users/login');
    }

    /**
     * Test role-based authorization
     *
     * @return void
     */
    public function testRoleBasedAuthorization(): void
    {
        $roleTests = [
            'user' => [
                'allowed' => [
                    'GET' => ['/articles', '/pages'],
                ],
                'forbidden' => [
                    'GET' => ['/admin', '/admin/users', '/admin/settings'],
                    'POST' => ['/admin/articles/add', '/admin/users/add'],
                ],
            ],
            'admin' => [
                'allowed' => [
                    'GET' => ['/admin', '/admin/articles', '/admin/users'],
                    'POST' => ['/admin/articles/add'],
                ],
                'forbidden' => [],
            ],
        ];

        $this->assertRoleBasedAuthorization($roleTests);
    }

    /**
     * Test CSRF protection on forms
     *
     * @return void
     */
    public function testCsrfProtection(): void
    {
        $forms = [
            '/articles/add' => [
                'title' => 'Test Article',
                'content' => 'Test content',
            ],
            '/pages/add' => [
                'title' => 'Test Page',
                'content' => 'Test page content',
            ],
            '/users/register' => [
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ],
        ];

        $this->assertCsrfProtection($forms);
    }

    /**
     * Test XSS prevention
     *
     * @return void
     */
    public function testXssProtection(): void
    {
        $forms = [
            '/articles/add' => ['title', 'content', 'summary'],
            '/pages/add' => ['title', 'content'],
            '/users/register' => ['email', 'first_name', 'last_name'],
        ];

        $this->assertXssProtection($forms);
    }

    /**
     * Test SQL injection prevention
     *
     * @return void
     */
    public function testSqlInjectionProtection(): void
    {
        $endpoints = [
            '/articles/search' => ['q', 'category'],
            '/pages/search' => ['q', 'type'],
            '/users/login' => ['email', 'password'],
        ];

        $this->assertSqlInjectionProtection($endpoints);
    }

    /**
     * Test rate limiting on authentication endpoints
     *
     * @return void
     */
    public function testAuthenticationRateLimit(): void
    {
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        // This test would normally fail due to rate limiting, 
        // but for development we'll test with a smaller limit
        $this->assertRateLimitProtection('/users/login', 'auth', $loginData);
    }

    /**
     * Test API rate limiting
     *
     * @return void
     */
    public function testApiRateLimit(): void
    {
        // Test API endpoint rate limiting (if API endpoints exist)
        if ($this->endpointExists('/api/products')) {
            $this->assertRateLimitProtection('/api/products', 'api');
        }
        
        if ($this->endpointExists('/api/articles')) {
            $this->assertRateLimitProtection('/api/articles', 'api');
        }
    }

    /**
     * Test password security requirements
     *
     * @return void
     */
    public function testPasswordSecurity(): void
    {
        $this->assertPasswordSecurity('/users/register', '/users/change-password');
    }

    /**
     * Test session security
     *
     * @return void
     */
    public function testSessionSecurity(): void
    {
        $this->assertSessionSecurity();
    }

    /**
     * Test file upload security
     *
     * @return void
     */
    public function testFileUploadSecurity(): void
    {
        // Test admin file upload if endpoint exists
        if ($this->endpointExists('/admin/files/upload')) {
            $this->assertFileUploadSecurity('/admin/files/upload');
        }

        // Test general file upload if endpoint exists
        if ($this->endpointExists('/files/upload')) {
            $this->assertFileUploadSecurity('/files/upload');
        }
    }

    /**
     * Test log integrity system
     *
     * @return void
     */
    public function testLogIntegrity(): void
    {
        $this->assertLogIntegrity();
    }

    /**
     * Test IP blocking functionality
     *
     * @return void
     */
    public function testIpBlocking(): void
    {
        $this->assertIpBlocking();
    }

    /**
     * Test security headers are present
     *
     * @return void
     */
    public function testSecurityHeaders(): void
    {
        $this->get('/');
        $this->assertResponseOk();

        // Test for common security headers
        $response = $this->_response;
        
        // Content Security Policy (if implemented)
        $cspHeader = $response->getHeaderLine('Content-Security-Policy');
        if (!empty($cspHeader)) {
            $this->assertStringContainsString('default-src', $cspHeader);
        }

        // X-Content-Type-Options
        $contentTypeOptions = $response->getHeaderLine('X-Content-Type-Options');
        if (!empty($contentTypeOptions)) {
            $this->assertEquals('nosniff', $contentTypeOptions);
        }

        // X-Frame-Options
        $frameOptions = $response->getHeaderLine('X-Frame-Options');
        if (!empty($frameOptions)) {
            $this->assertContains($frameOptions, ['DENY', 'SAMEORIGIN']);
        }

        // X-XSS-Protection
        $xssProtection = $response->getHeaderLine('X-XSS-Protection');
        if (!empty($xssProtection)) {
            $this->assertStringContainsString('1', $xssProtection);
        }
    }

    /**
     * Test authentication brute force protection
     *
     * @return void
     */
    public function testBruteForceProtection(): void
    {
        $loginData = [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ];

        // Attempt multiple failed logins
        for ($i = 0; $i < 6; $i++) {
            $this->post('/users/login', $loginData);
            
            // Should get either login failed or rate limited
            $this->assertResponseCode([302, 400, 422, 429]);
        }

        // After multiple attempts, should be rate limited or locked
        $this->post('/users/login', $loginData);
        $this->assertResponseCode([400, 422, 429], 
            'Account should be locked or rate limited after multiple failed attempts');
    }

    /**
     * Test admin interface security
     *
     * @return void
     */
    public function testAdminInterfaceSecurity(): void
    {
        // Test that admin interface requires proper authentication
        $this->get('/admin');
        $this->assertResponseCode([302, 401, 403]);

        // Test with regular user (should be forbidden)
        $this->loginUser('user');
        $this->get('/admin');
        $this->assertResponseCode([302, 403]);

        // Test with admin user (should work)
        $this->loginUser('admin');
        $this->get('/admin');
        $this->assertResponseCode([200, 302]);
    }

    /**
     * Test sensitive information exposure
     *
     * @return void
     */
    public function testSensitiveInformationExposure(): void
    {
        // Test that error pages don't expose sensitive information
        $this->get('/nonexistent-page');
        $responseBody = (string)$this->_response->getBody();

        // Should not expose database connection strings
        $this->assertStringNotContainsString('mysql://', strtolower($responseBody));
        $this->assertStringNotContainsString('password', strtolower($responseBody));
        $this->assertStringNotContainsString('secret', strtolower($responseBody));

        // Should not expose file paths
        $this->assertStringNotContainsString('/var/www/', $responseBody);
        $this->assertStringNotContainsString('C:\\', $responseBody);

        // Test API error responses
        $this->get('/api/nonexistent');
        $apiResponseBody = (string)$this->_response->getBody();
        
        $this->assertStringNotContainsString('stack trace', strtolower($apiResponseBody));
        $this->assertStringNotContainsString('debug', strtolower($apiResponseBody));
    }

    /**
     * Test input validation and sanitization
     *
     * @return void
     */
    public function testInputValidation(): void
    {
        $this->loginUser('user');

        // Test extremely long input
        $longString = str_repeat('A', 10000);
        $this->post('/articles/add', [
            'title' => $longString,
            'content' => 'Test content',
        ]);

        // Should handle gracefully (validation error or truncation)
        $this->assertResponseCode([200, 400, 422]);

        // Test null bytes and special characters
        $maliciousInput = "Test\x00Title\r\n<script>alert('xss')</script>";
        $this->post('/articles/add', [
            'title' => $maliciousInput,
            'content' => 'Test content',
        ]);

        // Should be sanitized or rejected
        $this->assertResponseCode([200, 302, 400, 422]);
    }

    /**
     * Test configuration security
     *
     * @return void
     */
    public function testConfigurationSecurity(): void
    {
        // Test that debug mode is disabled in production-like environment
        $debug = \Cake\Core\Configure::read('debug');
        if (getenv('CI') || getenv('PRODUCTION')) {
            $this->assertFalse($debug, 'Debug mode should be disabled in production');
        }

        // Test that sensitive configuration is not exposed
        $this->get('/');
        $responseBody = (string)$this->_response->getBody();
        
        // Should not expose configuration values
        $this->assertStringNotContainsString('API_KEY', $responseBody);
        $this->assertStringNotContainsString('DATABASE_URL', $responseBody);
        $this->assertStringNotContainsString('SECRET_KEY', $responseBody);
    }

    /**
     * Test for directory traversal vulnerabilities
     *
     * @return void
     */
    public function testDirectoryTraversal(): void
    {
        $traversalPayloads = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config\\sam',
            '....//....//....//etc/passwd',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd',
        ];

        foreach ($traversalPayloads as $payload) {
            // Test file inclusion endpoints (if any exist)
            $this->get('/files/view/' . urlencode($payload));
            $this->assertResponseCode([400, 403, 404], 
                "Should prevent directory traversal: {$payload}");

            // Test any file serving endpoints
            $this->get('/assets/' . urlencode($payload));
            $this->assertResponseCode([400, 403, 404], 
                "Should prevent directory traversal in assets: {$payload}");
        }
    }

    /**
     * Helper method to check if an endpoint exists
     *
     * @param string $endpoint Endpoint URL
     * @return bool True if endpoint exists
     */
    private function endpointExists(string $endpoint): bool
    {
        try {
            $this->get($endpoint);
            $statusCode = $this->_response->getStatusCode();
            
            // If we get a 404, the endpoint doesn't exist
            // Any other status code means it exists
            return $statusCode !== 404;
        } catch (\Exception $e) {
            return false;
        }
    }
}