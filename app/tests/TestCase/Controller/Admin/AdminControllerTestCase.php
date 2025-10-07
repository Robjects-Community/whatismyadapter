<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * AdminControllerTestCase
 *
 * Base test case for all Admin controller tests.
 * Provides common helper methods and authentication utilities.
 */
abstract class AdminControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Admin user credentials for testing
     *
     * @var array
     */
    protected array $adminUser = [
        'email' => 'admin@example.com',
        'password' => 'password',
    ];

    /**
     * Regular user credentials for testing
     *
     * @var array
     */
    protected array $regularUser = [
        'email' => 'user@example.com',
        'password' => 'password',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure request defaults
        $this->configRequest([
            'environment' => [
                'HTTPS' => 'on',
            ],
        ]);
    }

    /**
     * Login as admin user
     *
     * @return void
     */
    protected function loginAsAdmin(): void
    {
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'email' => $this->adminUser['email'],
                    'role' => 'admin',
                ],
            ],
        ]);
    }

    /**
     * Login as regular user
     *
     * @return void
     */
    protected function loginAsUser(): void
    {
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'email' => $this->regularUser['email'],
                    'role' => 'user',
                ],
            ],
        ]);
    }

    /**
     * Logout current user
     *
     * @return void
     */
    protected function logout(): void
    {
        $this->session([]);
    }

    /**
     * Get the ID of the first record from a fixture table
     *
     * @param string $tableName The name of the table (e.g., 'Articles', 'Products')
     * @return string UUID of the first record
     * @throws \RuntimeException If no fixture data is found
     */
    protected function getFirstFixtureId(string $tableName): string
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $entity = $table->find()->first();

        if (!$entity) {
            $this->fail("No fixture data found for table: {$tableName}");
        }

        return (string)$entity->id;
    }

    /**
     * Get a valid fixture ID with optional conditions
     *
     * @param string $tableName The name of the table
     * @param array $conditions Optional where conditions
     * @return string UUID of the matching record
     * @throws \RuntimeException If no matching record is found
     */
    protected function getValidFixtureId(string $tableName, array $conditions = []): string
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $query = $table->find();

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        $entity = $query->first();

        if (!$entity) {
            $conditionStr = !empty($conditions) ? ' with conditions' : '';
            $this->fail("No fixture data found for table: {$tableName}{$conditionStr}");
        }

        return (string)$entity->id;
    }

    /**
     * Get multiple fixture IDs
     *
     * @param string $tableName The name of the table
     * @param int $limit Maximum number of IDs to return
     * @param array $conditions Optional where conditions
     * @return array Array of UUIDs
     */
    protected function getMultipleFixtureIds(string $tableName, int $limit = 5, array $conditions = []): array
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $query = $table->find()->limit($limit);

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        $entities = $query->all();

        if ($entities->isEmpty()) {
            return [];
        }

        return $entities->extract('id')->toArray();
    }

    /**
     * Get fixture ID by field value
     *
     * @param string $tableName The name of the table
     * @param string $field Field name to search
     * @param mixed $value Value to match
     * @return string|null UUID of the matching record, null if not found
     */
    protected function getFixtureIdBy(string $tableName, string $field, $value): ?string
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $entity = $table->find()
            ->where([$field => $value])
            ->first();

        return $entity ? (string)$entity->id : null;
    }

    /**
     * Create a test entity in the database
     *
     * @param string $tableName The name of the table
     * @param array $data Entity data
     * @return string UUID of the created entity
     * @throws \RuntimeException If save fails
     */
    protected function createTestEntity(string $tableName, array $data): string
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $entity = $table->newEntity($data);

        if (!$table->save($entity)) {
            $this->fail("Failed to create test entity in {$tableName}: " . json_encode($entity->getErrors()));
        }

        return (string)$entity->id;
    }

    /**
     * Assert that a flash message was set
     *
     * @param string $expectedMessage The expected flash message
     * @param string $key Flash message key (default: 'flash')
     * @return void
     */
    protected function assertFlashMessage(string $expectedMessage, string $key = 'flash'): void
    {
        $flash = $this->_requestSession->read("Flash.{$key}");
        
        if (!$flash) {
            $this->fail("No flash message found with key: {$key}");
        }

        $message = is_array($flash) ? $flash[0]['message'] : $flash;
        $this->assertStringContainsString($expectedMessage, $message);
    }

    /**
     * Assert that a flash element was set with specific type
     *
     * @param string $expectedMessage The expected flash message
     * @param string $element Expected element type (e.g., 'success', 'error', 'warning')
     * @return void
     */
    protected function assertFlashElement(string $expectedMessage, string $element = 'success'): void
    {
        $flash = $this->_requestSession->read('Flash.flash');
        
        if (!$flash) {
            $this->fail('No flash message found');
        }

        $this->assertEquals($element, $flash[0]['element']);
        $this->assertStringContainsString($expectedMessage, $flash[0]['message']);
    }

    /**
     * Assert that response contains validation errors
     *
     * @param array $expectedErrors Expected error field names
     * @return void
     */
    protected function assertValidationErrors(array $expectedErrors): void
    {
        $this->assertResponseCode(200); // Form should be re-rendered
        
        foreach ($expectedErrors as $field) {
            $this->assertResponseContains("error");
        }
    }

    /**
     * Assert redirect to action
     *
     * @param array $expected Expected URL array (e.g., ['action' => 'index'])
     * @return void
     */
    protected function assertRedirectToAction(array $expected): void
    {
        $this->assertRedirect();
        
        $location = $this->_response->getHeaderLine('Location');
        
        foreach ($expected as $key => $value) {
            $this->assertStringContainsString($value, $location);
        }
    }

    /**
     * Get count of records in a table
     *
     * @param string $tableName The name of the table
     * @param array $conditions Optional where conditions
     * @return int Record count
     */
    protected function getRecordCount(string $tableName, array $conditions = []): int
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $query = $table->find();

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return $query->count();
    }

    /**
     * Assert that a record exists
     *
     * @param string $tableName The name of the table
     * @param array $conditions Where conditions
     * @return void
     */
    protected function assertRecordExists(string $tableName, array $conditions): void
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $exists = $table->exists($conditions);
        
        $this->assertTrue($exists, "Record not found in {$tableName} with conditions: " . json_encode($conditions));
    }

    /**
     * Assert that a record does not exist
     *
     * @param string $tableName The name of the table
     * @param array $conditions Where conditions
     * @return void
     */
    protected function assertRecordNotExists(string $tableName, array $conditions): void
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $exists = $table->exists($conditions);
        
        $this->assertFalse($exists, "Record found in {$tableName} but should not exist with conditions: " . json_encode($conditions));
    }
}
