<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

/**
 * Authentication Test Trait
 * 
 * Provides helper methods for testing authenticated and unauthenticated scenarios
 * in controller integration tests.
 */
trait AuthenticationTestTrait
{
    /**
     * Mock an authenticated regular user
     *
     * @param int $userId User ID to authenticate
     * @param string $role User role
     * @return void
     */
    protected function mockAuthenticatedUser(int $userId = 1, string $role = 'user'): void
    {
        $this->session([
            'Auth' => [
                'id' => $userId,
                'role' => $role,
                'email' => "user{$userId}@example.com",
                'active' => true,
            ]
        ]);
    }

    /**
     * Mock an authenticated admin user with full privileges
     *
     * @param int $userId Admin user ID
     * @return void
     */
    protected function mockAdminUser(int $userId = 1): void
    {
        $this->session([
            'Auth' => [
                'id' => $userId,
                'role' => 'admin',
                'email' => "admin{$userId}@example.com",
                'active' => true,
                'can_access_admin' => true,
            ]
        ]);
    }

    /**
     * Clear authentication session (mock unauthenticated user)
     *
     * @return void
     */
    protected function mockUnauthenticatedRequest(): void
    {
        $this->session([]);
    }

    /**
     * Assert that response redirects to login or home
     *
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertRedirectToLogin(string $message = ''): void
    {
        $this->assertRedirect(null, $message ?: 'Should redirect when not authenticated');
    }

    /**
     * Assert valid JSON response structure
     *
     * @param array $expectedKeys Expected top-level keys in JSON response
     * @return void
     */
    protected function assertJsonResponse(array $expectedKeys = []): void
    {
        $this->assertContentType('application/json');
        
        if (!empty($expectedKeys)) {
            $response = json_decode((string)$this->_response->getBody(), true);
            $this->assertIsArray($response, 'Response body should be valid JSON');
            
            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $response, "JSON response missing key: {$key}");
            }
        }
    }

    /**
     * Enable CSRF token for form submissions
     *
     * @return void
     */
    protected function enableCsrf(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

    /**
     * Assert response code is one of the expected codes
     *
     * @param array|int $expectedCodes Expected HTTP status codes
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertResponseCode($expectedCodes, string $message = ''): void
    {
        $expectedCodes = (array)$expectedCodes;
        $actualCode = $this->_response->getStatusCode();
        
        $this->assertContains(
            $actualCode,
            $expectedCodes,
            $message ?: sprintf(
                'Response code %d not in expected codes: %s',
                $actualCode,
                implode(', ', $expectedCodes)
            )
        );
    }
}
