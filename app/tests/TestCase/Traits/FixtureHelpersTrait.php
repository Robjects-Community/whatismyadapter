<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * FixtureHelpersTrait
 *
 * Provides utilities for dynamic fixture data generation and test data manipulation:
 * - Dynamic entity creation
 * - Test data factories
 * - Fixture data cleanup
 * - Related data setup helpers
 *
 * Usage in test classes:
 * ```php
 * use App\Test\TestCase\Traits\FixtureHelpersTrait;
 *
 * class MyTest extends TestCase
 * {
 *     use FixtureHelpersTrait;
 *
 *     public function testSomething(): void
 *     {
 *         $user = $this->createTestUser(['username' => 'testuser']);
 *         // ... test code
 *     }
 * }
 * ```
 */
trait FixtureHelpersTrait
{
    // ============================================================
    // Dynamic Entity Creation
    // ============================================================

    /**
     * Create a test user with default or custom data
     *
     * @param array $overrides Custom field values
     * @return \Cake\Datasource\EntityInterface
     */
    protected function createTestUser(array $overrides = []): \Cake\Datasource\EntityInterface
    {
        $defaults = [
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => 'TestPassword123',
            'confirm_password' => 'TestPassword123',
            'role' => 'user',
            'active' => 1,
        ];
        
        $data = array_merge($defaults, $overrides);
        
        $users = $this->getTableLocator()->get('Users');
        $user = $users->newEntity($data);
        
        return $users->save($user);
    }

    /**
     * Create a test article with default or custom data
     *
     * @param array $overrides Custom field values
     * @return \Cake\Datasource\EntityInterface
     */
    protected function createTestArticle(array $overrides = []): \Cake\Datasource\EntityInterface
    {
        $defaults = [
            'title' => 'Test Article ' . uniqid(),
            'body' => 'This is a test article body content.',
            'kind' => 'article',
            'is_published' => 1,
            'published' => new DateTime(),
        ];
        
        // Auto-create user if user_id not provided
        if (!isset($overrides['user_id'])) {
            $user = $this->createTestUser();
            $defaults['user_id'] = $user->id;
        }
        
        $data = array_merge($defaults, $overrides);
        
        $articles = $this->getTableLocator()->get('Articles');
        $article = $articles->newEntity($data);
        
        return $articles->save($article);
    }

    /**
     * Create a test entity for any table
     *
     * @param string $tableName Table name
     * @param array $data Entity data
     * @param array $options Save options
     * @return \Cake\Datasource\EntityInterface
     */
    protected function createTestEntity(
        string $tableName,
        array $data = [],
        array $options = []
    ): \Cake\Datasource\EntityInterface {
        $table = $this->getTableLocator()->get($tableName);
        $entity = $table->newEntity($data, $options);
        
        return $table->save($entity);
    }

    /**
     * Create multiple test entities at once
     *
     * @param string $tableName Table name
     * @param array $records Array of entity data
     * @return array Array of created entities
     */
    protected function createMultipleTestEntities(
        string $tableName,
        array $records
    ): array {
        $table = $this->getTableLocator()->get($tableName);
        $entities = [];
        
        foreach ($records as $data) {
            $entity = $table->newEntity($data);
            $saved = $table->save($entity);
            if ($saved) {
                $entities[] = $saved;
            }
        }
        
        return $entities;
    }

    // ============================================================
    // Data Generation Helpers
    // ============================================================

    /**
     * Generate a unique email address
     *
     * @param string $prefix Email prefix
     * @return string
     */
    protected function generateUniqueEmail(string $prefix = 'test'): string
    {
        return $prefix . '_' . uniqid() . '@example.com';
    }

    /**
     * Generate a unique username
     *
     * @param string $prefix Username prefix
     * @return string
     */
    protected function generateUniqueUsername(string $prefix = 'user'): string
    {
        return $prefix . '_' . uniqid();
    }

    /**
     * Generate random string of specific length
     *
     * @param int $length String length
     * @param string $chars Characters to use
     * @return string
     */
    protected function generateRandomString(
        int $length = 10,
        string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    ): string {
        return substr(str_shuffle(str_repeat($chars, (int)ceil($length / strlen($chars)))), 0, $length);
    }

    /**
     * Generate test data for a field based on its validation rules
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $field Field name
     * @param bool $valid Whether to generate valid or invalid data
     * @return mixed
     */
    protected function generateTestDataForField(
        Table $table,
        string $field,
        bool $valid = true
    ) {
        $validator = $table->getValidator();
        $fieldRules = $validator->field($field);
        
        // Basic implementation - can be expanded based on validation rules
        if ($valid) {
            return $this->generateValidDataForField($field, $fieldRules);
        } else {
            return $this->generateInvalidDataForField($field, $fieldRules);
        }
    }

    /**
     * Generate valid data for a field
     *
     * @param string $field Field name
     * @param mixed $rules Validation rules
     * @return mixed
     */
    private function generateValidDataForField(string $field, $rules)
    {
        // Simple implementation - can be expanded
        if (str_contains($field, 'email')) {
            return $this->generateUniqueEmail();
        }
        if (str_contains($field, 'username')) {
            return $this->generateUniqueUsername();
        }
        if (str_contains($field, 'password')) {
            return 'ValidPassword123';
        }
        
        return 'Valid ' . $field . ' value';
    }

    /**
     * Generate invalid data for a field
     *
     * @param string $field Field name
     * @param mixed $rules Validation rules
     * @return mixed
     */
    private function generateInvalidDataForField(string $field, $rules)
    {
        // Simple implementation - can be expanded
        if (str_contains($field, 'email')) {
            return 'invalid-email';
        }
        
        return ''; // Empty is often invalid
    }

    // ============================================================
    // Related Data Setup
    // ============================================================

    /**
     * Create a complete test article with related data (user, tags, comments)
     *
     * @param array $options Configuration options
     * @return array ['article' => entity, 'user' => entity, 'tags' => array, 'comments' => array]
     */
    protected function createArticleWithRelatedData(array $options = []): array
    {
        $result = [];
        
        // Create user
        $result['user'] = $this->createTestUser($options['user'] ?? []);
        
        // Create article
        $articleData = array_merge(
            ['user_id' => $result['user']->id],
            $options['article'] ?? []
        );
        $result['article'] = $this->createTestArticle($articleData);
        
        // Create tags if requested
        if (isset($options['tags'])) {
            $result['tags'] = $this->createTestTags(
                $result['article']->id,
                $options['tags']
            );
        }
        
        // Create comments if requested
        if (isset($options['comments'])) {
            $result['comments'] = $this->createTestComments(
                $result['article']->id,
                $result['user']->id,
                $options['comments']
            );
        }
        
        return $result;
    }

    /**
     * Create test tags and associate with an article
     *
     * @param string $articleId Article ID
     * @param array $tagNames Array of tag names
     * @return array Created tag entities
     */
    protected function createTestTags(string $articleId, array $tagNames): array
    {
        $tags = $this->getTableLocator()->get('Tags');
        $articles = $this->getTableLocator()->get('Articles');
        
        $tagEntities = [];
        
        foreach ($tagNames as $name) {
            $tag = $tags->find()
                ->where(['name' => $name])
                ->first();
            
            if (!$tag) {
                $tag = $tags->newEntity(['name' => $name, 'slug' => Text::slug($name)]);
                $tag = $tags->save($tag);
            }
            
            $tagEntities[] = $tag;
        }
        
        // Associate tags with article
        $article = $articles->get($articleId);
        $article->tags = $tagEntities;
        $articles->save($article);
        
        return $tagEntities;
    }

    /**
     * Create test comments for an article
     *
     * @param string $articleId Article ID
     * @param string $userId User ID
     * @param int $count Number of comments to create
     * @return array Created comment entities
     */
    protected function createTestComments(
        string $articleId,
        string $userId,
        int $count = 3
    ): array {
        $comments = $this->getTableLocator()->get('Comments');
        $entities = [];
        
        for ($i = 0; $i < $count; $i++) {
            $comment = $comments->newEntity([
                'article_id' => $articleId,
                'user_id' => $userId,
                'body' => "Test comment #{$i}",
                'approved' => 1,
            ]);
            
            $saved = $comments->save($comment);
            if ($saved) {
                $entities[] = $saved;
            }
        }
        
        return $entities;
    }

    // ============================================================
    // Test Data Cleanup
    // ============================================================

    /**
     * Delete all test entities created for a specific table
     *
     * @param string $tableName Table name
     * @param array $conditions Additional conditions
     * @return int Number of deleted records
     */
    protected function cleanupTestData(string $tableName, array $conditions = []): int
    {
        $table = $this->getTableLocator()->get($tableName);
        
        return $table->deleteAll($conditions);
    }

    /**
     * Delete all test users with specific prefix
     *
     * @param string $prefix Username prefix
     * @return int Number of deleted records
     */
    protected function cleanupTestUsers(string $prefix = 'testuser_'): int
    {
        return $this->cleanupTestData('Users', ['username LIKE' => $prefix . '%']);
    }

    /**
     * Delete all test articles with specific prefix
     *
     * @param string $prefix Title prefix
     * @return int Number of deleted records
     */
    protected function cleanupTestArticles(string $prefix = 'Test Article '): int
    {
        return $this->cleanupTestData('Articles', ['title LIKE' => $prefix . '%']);
    }

    /**
     * Truncate a table (delete all records)
     *
     * Use with caution!
     *
     * @param string $tableName Table name
     * @return int Number of deleted records
     */
    protected function truncateTable(string $tableName): int
    {
        $table = $this->getTableLocator()->get($tableName);
        
        return $table->deleteAll([]);
    }

    // ============================================================
    // Fixture Data Manipulation
    // ============================================================

    /**
     * Extract fixture data from an entity
     *
     * Useful for updating fixtures with real test data
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to extract from
     * @param array $excludeFields Fields to exclude
     * @return array
     */
    protected function extractFixtureData(
        \Cake\Datasource\EntityInterface $entity,
        array $excludeFields = []
    ): array {
        $data = $entity->toArray();
        
        // Exclude fields
        foreach ($excludeFields as $field) {
            unset($data[$field]);
        }
        
        // Convert DateTime objects to strings
        foreach ($data as $key => $value) {
            if ($value instanceof DateTime) {
                $data[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        
        return $data;
    }

    /**
     * Generate fixture record template
     *
     * @param string $tableName Table name
     * @param array $sampleData Sample data
     * @return string PHP code for fixture record
     */
    protected function generateFixtureRecord(
        string $tableName,
        array $sampleData
    ): string {
        $table = $this->getTableLocator()->get($tableName);
        $schema = $table->getSchema();
        
        $record = [];
        
        foreach ($schema->columns() as $column) {
            if (isset($sampleData[$column])) {
                $record[$column] = $sampleData[$column];
            } else {
                // Generate default value based on column type
                $record[$column] = $this->getDefaultFixtureValue($schema->getColumnType($column));
            }
        }
        
        return var_export($record, true);
    }

    /**
     * Get default fixture value for a column type
     *
     * @param string $type Column type
     * @return mixed
     */
    private function getDefaultFixtureValue(string $type)
    {
        $defaults = [
            'string' => 'Lorem ipsum',
            'text' => 'Lorem ipsum dolor sit amet',
            'integer' => 1,
            'biginteger' => 1,
            'float' => 1.0,
            'decimal' => '1.00',
            'boolean' => true,
            'date' => '2025-01-01',
            'datetime' => '2025-01-01 00:00:00',
            'timestamp' => '2025-01-01 00:00:00',
            'time' => '00:00:00',
            'uuid' => Text::uuid(),
            'json' => [],
        ];
        
        return $defaults[$type] ?? null;
    }

    // ============================================================
    // Batch Operations
    // ============================================================

    /**
     * Create a batch of test entities with sequential data
     *
     * @param string $tableName Table name
     * @param int $count Number of entities to create
     * @param array $template Template data
     * @param callable|null $modifier Optional function to modify each record
     * @return array Created entities
     */
    protected function createBatchTestEntities(
        string $tableName,
        int $count,
        array $template = [],
        ?callable $modifier = null
    ): array {
        $entities = [];
        
        for ($i = 0; $i < $count; $i++) {
            $data = $template;
            
            // Apply modifier if provided
            if ($modifier !== null) {
                $data = $modifier($data, $i);
            }
            
            $entities[] = $this->createTestEntity($tableName, $data);
        }
        
        return $entities;
    }

    /**
     * Assert that test data was properly cleaned up
     *
     * @param string $tableName Table name
     * @param array $conditions Conditions to check
     * @return void
     */
    protected function assertTestDataCleanedUp(
        string $tableName,
        array $conditions
    ): void {
        $table = $this->getTableLocator()->get($tableName);
        $count = $table->find()->where($conditions)->count();
        
        $this->assertEquals(
            0,
            $count,
            "Expected test data to be cleaned up in {$tableName} but found {$count} records"
        );
    }
}
