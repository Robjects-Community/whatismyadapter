<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

/**
 * Authentication Test Trait
 * 
 * Provides helper methods for testing authenticated and unauthenticated scenarios
 * in controller integration tests.
 * 
 * This trait works with CakePHP's Authentication and Authorization plugins.
 * It properly loads User entities from fixtures and stores them in the session
 * so they can be retrieved by the Authentication component.
 */
trait AuthenticationTestTrait
{
    /**
     * Mock an authenticated regular user
     *
     * @param string $userId User ID to authenticate (defaults to regular user UUID from UsersFixture)
     * @param string $role User role (defaults to 'user')
     * @return void
     */
    protected function mockAuthenticatedUser(string $userId = '91d91e66-5d90-412b-aeaa-4d51fa110795', string $role = 'user'): void
    {
        // Load the actual User entity from the database (populated by fixtures)
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($userId);
        
        // Ensure the role is set correctly
        if ($user->role !== $role) {
            $user->role = $role;
            $usersTable->save($user);
        }
        
        // Store the FULL User entity in session - Authentication plugin expects an entity object
        // that implements IdentityInterface, not just an array
        $this->session([
            'Auth' => $user
        ]);
    }

    /**
     * Mock an authenticated admin user with full privileges
     *
     * @param string $userId Admin user ID (defaults to admin UUID from UsersFixture)
     * @return void
     */
    protected function mockAdminUser(string $userId = '90d91e66-5d90-412b-aeaa-4d51fa110794'): void
    {
        // Load the actual User entity from the database
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($userId);
        
        // Ensure admin permissions are set
        $user->role = 'admin';
        $user->is_admin = true;
        $usersTable->save($user);
        
        // Store the FULL User entity in session - Authentication plugin expects an entity object
        $this->session([
            'Auth' => $user
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
     * Note: Use this instead of assertResponseCode() when checking multiple possible codes
     *
     * @param array|int $expectedCodes Expected HTTP status codes
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertResponseCodeIn($expectedCodes, string $message = ''): void
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
