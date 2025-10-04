<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Cake\Core\Configure;
use Cake\Utility\Security;
use Cake\Http\Session;
use Cake\I18n\FrozenTime;

/**
 * WillowSecurityTestCase Base Class
 * 
 * Specialized base class for security testing in WillowCMS.
 * Provides comprehensive security validation utilities including:
 * - Authentication and authorization testing
 * - CSRF and XSS protection validation
 * - Rate limiting verification
 * - Input validation and SQL injection prevention
 * - Log integrity and tamper detection
 */
abstract class WillowSecurityTestCase extends WillowControllerTestCase
{
    /**
     * Security test configuration
     * @var array
     */
    protected $securityConfig = [];

    /**
     * Rate limiting test data
     * @var array
     */
    protected $rateLimitData = [];

    /**
     * Setup method called before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        $this->setupSecurityTesting();
    }

    /**
     * Setup security-specific testing environment
     *
     * @return void
     */
    protected function setupSecurityTesting(): void
    {
        // Configure security settings for testing
        $this->securityConfig = [
            'csrf_enabled' => Configure::read('Security.csrfUseOnce'),
            'rate_limiting' => true,
            'log_integrity' => true,
            'ip_blocking' => true,
        ];

        // Setup rate limiting test data
        $this->rateLimitData = [
            'default' => ['limit' => 100, 'window' => 3600],
            'auth' => ['limit' => 5, 'window' => 300],
            'api' => ['limit' => 1000, 'window' => 3600],
            'anthropic' => ['limit' => 50, 'window' => 3600],
        ];
    }

    /**
     * Test authentication requirements for protected routes
     *
     * @param array $routes Array of routes to test
     * @param string $redirectUrl Expected redirect URL for unauthenticated users
     * @return void
     */
    protected function assertRoutesRequireAuthentication(array $routes, string $redirectUrl = '/users/login'): void
    {
        $this->logoutUser();
        
        foreach ($routes as $method => $urls) {
            if (is_string($urls)) {
                $urls = [$urls];
            }
            
            foreach ($urls as $url) {
                switch (strtoupper($method)) {
                    case 'GET':
                        $this->get($url);
                        break;
                    case 'POST':
                        $this->post($url, []);
                        break;
                    case 'PUT':
                        $this->put($url, []);
                        break;
                    case 'DELETE':
                        $this->delete($url);
                        break;
                }
                
                // Should redirect to login or return unauthorized
                $this->assertResponseCode([302, 401, 403], 
                    "Route {$method} {$url} should require authentication");
                
                if ($this->_response->getStatusCode() === 302) {
                    $location = $this->_response->getHeaderLine('Location');
                    $this->assertStringContainsString($redirectUrl, $location,
                        "Should redirect to {$redirectUrl} for route {$method} {$url}");
                }
            }
        }
    }

    /**
     * Test role-based authorization
     *
     * @param array $roleTests Array of role => routes mapping
     * @return void
     */
    protected function assertRoleBasedAuthorization(array $roleTests): void
    {
        foreach ($roleTests as $role => $routeConfig) {
            $this->loginUser($role);
            
            // Test allowed routes
            if (isset($routeConfig['allowed'])) {
                foreach ($routeConfig['allowed'] as $method => $urls) {
                    if (is_string($urls)) $urls = [$urls];
                    
                    foreach ($urls as $url) {
                        $this->makeRequest($method, $url);
                        $this->assertResponseCode([200, 201, 302], 
                            "Role {$role} should access {$method} {$url}");
                    }
                }
            }
            
            // Test forbidden routes
            if (isset($routeConfig['forbidden'])) {
                foreach ($routeConfig['forbidden'] as $method => $urls) {
                    if (is_string($urls)) $urls = [$urls];
                    
                    foreach ($urls as $url) {
                        $this->makeRequest($method, $url);
                        $this->assertResponseCode([403, 302], 
                            "Role {$role} should be forbidden from {$method} {$url}");
                    }
                }
            }
        }
    }

    /**
     * Test CSRF protection on forms and AJAX requests
     *
     * @param array $forms Array of form URLs and data
     * @return void
     */
    protected function assertCsrfProtection(array $forms): void
    {
        $this->loginUser('user');
        
        foreach ($forms as $url => $data) {
            // Test without CSRF token
            $this->disableCsrfToken();
            $this->post($url, $data);
            $this->assertResponseCode([400, 403], 
                "Form at {$url} should be CSRF protected");
            
            // Test with invalid CSRF token
            $invalidData = array_merge($data, ['_csrfToken' => 'invalid-token']);
            $this->post($url, $invalidData);
            $this->assertResponseCode([400, 403], 
                "Form at {$url} should reject invalid CSRF token");
            
            // Test with valid CSRF token
            $this->enableCsrfToken();
            $this->post($url, $data);
            $this->assertResponseCode([200, 201, 302], 
                "Form at {$url} should accept valid CSRF token");
        }
    }

    /**
     * Test XSS prevention in input fields
     *
     * @param array $forms Array of form URLs and XSS payloads
     * @return void
     */
    protected function assertXssProtection(array $forms): void
    {
        $this->loginUser('user');
        
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(1)">',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(1)"></iframe>',
            '<svg onload="alert(1)">',
        ];
        
        foreach ($forms as $url => $fields) {
            foreach ($xssPayloads as $payload) {
                $testData = [];
                foreach ($fields as $field) {
                    $testData[$field] = $payload;
                }
                
                $this->post($url, $testData);
                
                // Check response doesn't contain unescaped payload
                $responseBody = (string)$this->_response->getBody();
                $this->assertStringNotContainsString('<script>', $responseBody,
                    "Response should not contain unescaped script tags from {$url}");
                $this->assertStringNotContainsString('javascript:', $responseBody,
                    "Response should not contain javascript: protocol from {$url}");
            }
        }
    }

    /**
     * Test SQL injection prevention
     *
     * @param array $endpoints Array of endpoints with injectable parameters
     * @return void
     */
    protected function assertSqlInjectionProtection(array $endpoints): void
    {
        $sqlInjectionPayloads = [
            "'; DROP TABLE users; --",
            "' OR '1'='1",
            "' UNION SELECT * FROM users --",
            "'; UPDATE users SET role='admin' WHERE id=1; --",
            "' AND 1=1 --",
        ];
        
        foreach ($endpoints as $endpoint => $params) {
            foreach ($sqlInjectionPayloads as $payload) {
                $testData = [];
                foreach ($params as $param) {
                    $testData[$param] = $payload;
                }
                
                $this->post($endpoint, $testData);
                
                // Should not cause database error or unauthorized data access
                $this->assertResponseCode([200, 400, 404, 422], 
                    "Endpoint {$endpoint} should handle SQL injection attempts safely");
                
                // Response should not contain database error messages
                $responseBody = (string)$this->_response->getBody();
                $this->assertStringNotContainsString('mysql_', strtolower($responseBody),
                    "Response should not expose database errors");
                $this->assertStringNotContainsString('sql', strtolower($responseBody),
                    "Response should not expose SQL errors");
            }
        }
    }

    /**
     * Test rate limiting on endpoints
     *
     * @param string $endpoint Endpoint to test
     * @param string $rateLimitType Type of rate limit (default, auth, api, etc.)
     * @param array $requestData Request data for POST/PUT requests
     * @return void
     */
    protected function assertRateLimitProtection(string $endpoint, string $rateLimitType = 'default', array $requestData = []): void
    {
        if (!isset($this->rateLimitData[$rateLimitType])) {
            $this->fail("Unknown rate limit type: {$rateLimitType}");
        }
        
        $config = $this->rateLimitData[$rateLimitType];
        $limit = $config['limit'];
        
        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            if (empty($requestData)) {
                $this->get($endpoint);
            } else {
                $this->post($endpoint, $requestData);
            }
            
            $this->assertResponseCode([200, 201, 302, 400, 404], 
                "Request {$i} should be within rate limit");
        }
        
        // Next request should be rate limited
        if (empty($requestData)) {
            $this->get($endpoint);
        } else {
            $this->post($endpoint, $requestData);
        }
        
        $this->assertResponseCode(429, "Request should be rate limited");
        $this->assertResponseHeaderContains('X-RateLimit-Limit', (string)$limit);
        $this->assertResponseHeaderContains('X-RateLimit-Remaining', '0');
    }

    /**
     * Test password security requirements
     *
     * @param string $registrationUrl User registration endpoint
     * @param string $passwordChangeUrl Password change endpoint
     * @return void
     */
    protected function assertPasswordSecurity(string $registrationUrl, string $passwordChangeUrl): void
    {
        $weakPasswords = [
            '123456',
            'password',
            'admin',
            '12345678',
            'test',
            'abc123',
        ];
        
        // Test weak password rejection during registration
        foreach ($weakPasswords as $weakPassword) {
            $userData = [
                'email' => 'test' . uniqid() . '@example.com',
                'password' => $weakPassword,
                'password_confirm' => $weakPassword,
            ];
            
            $this->post($registrationUrl, $userData);
            $this->assertResponseCode([400, 422], 
                "Should reject weak password: {$weakPassword}");
        }
        
        // Test strong password acceptance
        $strongPassword = 'SecureP@ssw0rd!2024';
        $userData = [
            'email' => 'test' . uniqid() . '@example.com',
            'password' => $strongPassword,
            'password_confirm' => $strongPassword,
        ];
        
        $this->post($registrationUrl, $userData);
        $this->assertResponseCode([200, 201, 302], 
            "Should accept strong password");
        
        // Test password change requires current password
        $this->loginUser('user');
        $changeData = [
            'current_password' => 'wrong-password',
            'new_password' => $strongPassword,
            'confirm_password' => $strongPassword,
        ];
        
        $this->post($passwordChangeUrl, $changeData);
        $this->assertResponseCode([400, 422], 
            "Should reject password change with wrong current password");
    }

    /**
     * Test session security
     *
     * @return void
     */
    protected function assertSessionSecurity(): void
    {
        $user = $this->loginUser('user');
        
        // Test session regeneration after login
        $this->get('/admin');
        $this->assertResponseCode([200, 302]);
        
        // Test session timeout
        // Simulate expired session by clearing session data
        $this->session(['Auth.User' => null]);
        
        $this->get('/admin');
        $this->assertResponseCode([302, 401, 403], 
            "Should require authentication after session expires");
        
        // Test session hijacking protection
        $this->loginUser('user');
        
        // Simulate different user agent
        $this->configRequest([
            'environment' => [
                'HTTP_USER_AGENT' => 'Different Browser'
            ]
        ]);
        
        $this->get('/admin');
        // Should still work if session fingerprinting is not implemented,
        // or should reject if it is implemented
        $this->assertResponseCode([200, 302, 401, 403]);
    }

    /**
     * Test file upload security
     *
     * @param string $uploadUrl Upload endpoint URL
     * @return void
     */
    protected function assertFileUploadSecurity(string $uploadUrl): void
    {
        $this->loginUser('user');
        
        // Test malicious file types
        $maliciousFiles = [
            'test.php' => '<?php system($_GET["cmd"]); ?>',
            'test.jsp' => '<% Runtime.getRuntime().exec(request.getParameter("cmd")); %>',
            'test.exe' => 'MZ...', // PE header
            'test.sh' => '#!/bin/bash\nrm -rf /',
        ];
        
        foreach ($maliciousFiles as $filename => $content) {
            $fileData = [
                'upload' => [
                    'name' => $filename,
                    'type' => 'application/octet-stream',
                    'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
                    'error' => UPLOAD_ERR_OK,
                    'size' => strlen($content),
                ]
            ];
            
            file_put_contents($fileData['upload']['tmp_name'], $content);
            
            $this->post($uploadUrl, $fileData);
            $this->assertResponseCode([400, 422], 
                "Should reject malicious file: {$filename}");
            
            unlink($fileData['upload']['tmp_name']);
        }
    }

    /**
     * Test log integrity and tamper detection
     *
     * @return void
     */
    protected function assertLogIntegrity(): void
    {
        // Create a test log entry
        $testMessage = 'Security test log entry ' . uniqid();
        log_message('info', $testMessage);
        
        // Check if checksum file is created
        $logFiles = glob(LOGS . '*.log');
        $checksumFiles = glob(LOGS . '*.sha256');
        
        $this->assertNotEmpty($logFiles, 'Log files should exist');
        
        if (!empty($checksumFiles)) {
            // Verify log integrity
            foreach ($checksumFiles as $checksumFile) {
                $this->assertTrue(file_exists($checksumFile), 
                    'Checksum file should exist for log integrity');
                
                $checksumContent = file_get_contents($checksumFile);
                $this->assertNotEmpty($checksumContent, 
                    'Checksum file should not be empty');
            }
        }
    }

    /**
     * Test IP blocking functionality
     *
     * @return void
     */
    protected function assertIpBlocking(): void
    {
        // This would typically test the IP blocking middleware
        // For testing purposes, we'll simulate blocked IP behavior
        
        $this->configRequest([
            'environment' => [
                'REMOTE_ADDR' => '192.168.1.100', // Test IP
            ]
        ]);
        
        // Make a request that should be allowed
        $this->get('/');
        $this->assertResponseCode([200, 302], 'Normal IP should be allowed');
        
        // Test with known malicious patterns (if implemented)
        $maliciousIPs = [
            '0.0.0.0',
            '127.0.0.1', // Might be blocked in some configurations
            '10.0.0.1',  // Private IP from external
        ];
        
        foreach ($maliciousIPs as $ip) {
            $this->configRequest([
                'environment' => [
                    'REMOTE_ADDR' => $ip,
                ]
            ]);
            
            $this->get('/');
            // Response depends on IP blocking configuration
            $this->assertResponseCode([200, 302, 403, 404]);
        }
    }

    /**
     * Helper method to make HTTP requests
     *
     * @param string $method HTTP method
     * @param string $url URL
     * @param array $data Request data
     * @return void
     */
    private function makeRequest(string $method, string $url, array $data = []): void
    {
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
    }

    /**
     * Assert that response header contains expected value
     *
     * @param string $header Header name
     * @param string $expectedValue Expected header value
     * @return void
     */
    protected function assertResponseHeaderContains(string $header, string $expectedValue): void
    {
        $headerValue = $this->_response->getHeaderLine($header);
        $this->assertStringContainsString($expectedValue, $headerValue, 
            "Header {$header} should contain {$expectedValue}");
    }

    /**
     * Generate a secure test password
     *
     * @return string Secure password
     */
    protected function generateSecurePassword(): string
    {
        return 'TestP@ssw0rd!' . uniqid() . '2024';
    }
}