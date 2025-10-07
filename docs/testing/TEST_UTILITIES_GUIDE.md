# Test Utilities Guide - WillowCMS

**Location:** `app/tests/TestCase/Traits/`

**Purpose:** Reusable testing utilities for WillowCMS comprehensive test suite

---

## Overview

Three comprehensive trait files provide reusable testing utilities:

1. **MockServicesTrait** - Mock external services (AI, Queue, Cache, Filesystem, HTTP, Email)
2. **AssertionHelpersTrait** - Custom assertions for CakePHP testing
3. **FixtureHelpersTrait** - Dynamic fixture data generation and test data management

---

## 1. MockServicesTrait

**File:** `app/tests/TestCase/Traits/MockServicesTrait.php`

### Features

- ✅ AI service mocking (Anthropic, Google)
- ✅ Queue system simulation
- ✅ Cache layer mocking
- ✅ Filesystem operations mocking
- ✅ HTTP client mocking
- ✅ Time manipulation
- ✅ Email transport mocking

### Usage Examples

```php
<?php
use App\Test\TestCase\Traits\MockServicesTrait;

class MyServiceTest extends TestCase
{
    use MockServicesTrait;
    
    public function testAiIntegration(): void
    {
        // Mock Anthropic AI service
        $mockAi = $this->mockAnthropicService([
            ['response' => 'AI generated text'],
        ]);
        
        // Test code using mocked AI
    }
    
    public function testQueueJob(): void
    {
        // Spy on queued jobs
        $jobs = $this->spyQueuedJobs();
        
        // Code that queues jobs
        $this->service->processData();
        
        // Assert job was queued
        $this->assertJobWasQueued('App\\Job\\DataProcessingJob', [
            'data' => 'test',
        ]);
    }
    
    public function testCacheUsage(): void
    {
        // Use mock cache
        $this->useMockCache();
        
        // Seed cache with test data
        $this->seedCache([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        
        // Test code
        
        // Assert cache has specific key
        $this->assertCacheHas('key1');
    }
    
    public function testTimeBasedLogic(): void
    {
        // Freeze time
        $this->freezeTime('2025-01-01 12:00:00');
        
        // Test code that depends on current time
        
        // Travel forward
        $this->travelTo('+1 day');
        
        // More tests
        
        // Unfreeze time
        $this->unfreezeTime();
    }
    
    public function testEmailSending(): void
    {
        // Mock email transport
        $emails = $this->mockEmailTransport();
        
        // Code that sends emails
        $this->mailer->send();
        
        // Assert email was sent
        $this->assertEmailWasSent('user@example.com', 'Welcome');
    }
}
```

### Available Methods

#### AI Services
- `mockAnthropicService(array $responses)` - Mock Anthropic
- `mockGoogleService(array $config)` - Mock Google AI
- `mockAiMetricsRecording()` - Prevent AI metrics writes
- `createAiApiResponses(string $service, array $responses)` - Create HTTP responses

#### Queue
- `mockQueueSynchronousExecution()` - Run jobs synchronously
- `spyQueuedJobs()` - Track queued jobs
- `assertJobWasQueued(string $jobClass, array $expectedData)` - Assert job queued

#### Cache
- `useMockCache(string $config)` - Use array cache
- `clearTestCache(string $config)` - Clear cache
- `seedCache(array $data, string $config)` - Add test data
- `assertCacheHas(string $key, string $config)` - Assert key exists
- `assertCacheEmpty(string $config)` - Assert cache empty

#### Filesystem
- `mockFilesystem(array $existingFiles)` - Mock filesystem
- `createTempTestDir()` - Create temp directory
- `cleanupTempTestDirs()` - Remove temp directories

#### HTTP
- `createMockHttpClient(array $responses)` - Mock HTTP client
- `createSuccessResponse(array $data, int $status)` - Create 2xx response
- `createErrorResponse(string $message, int $status)` - Create error response

#### Time
- `freezeTime($time)` - Freeze time
- `unfreezeTime()` - Unfreeze time
- `travelTo(string $interval)` - Travel in time

#### Email
- `mockEmailTransport()` - Mock email sending
- `assertEmailWasSent(string $to, ?string $subject)` - Assert email sent

---

## 2. AssertionHelpersTrait

**File:** `app/tests/TestCase/Traits/AssertionHelpersTrait.php`

### Features

- ✅ Validation error assertions
- ✅ Association testing
- ✅ Behavior verification
- ✅ Entity state checking
- ✅ Database assertions
- ✅ Collection/array assertions
- ✅ JSON assertions
- ✅ HTTP response assertions

### Usage Examples

```php
<?php
use App\Test\TestCase\Traits\AssertionHelpersTrait;

class UsersTableTest extends TestCase
{
    use AssertionHelpersTrait;
    
    public function testValidationErrors(): void
    {
        $user = $this->Users->newEntity([
            'email' => 'invalid-email',
        ]);
        
        // Assert has validation error
        $this->assertHasValidationError($user, 'email', 'email');
        
        // Assert multiple errors
        $this->assertHasValidationErrors($user, [
            'username' => '_required',
            'password' => '_required',
        ]);
        
        // Assert error message contains text
        $this->assertValidationErrorContains($user, 'email', 'valid email');
    }
    
    public function testAssociations(): void
    {
        // Assert has association
        $this->assertHasAssociation($this->Users, 'Articles');
        
        // Assert association type
        $this->assertAssociationType($this->Users, 'Articles', 'HasMany');
        
        // Assert association target
        $this->assertAssociationTarget($this->Users, 'Articles', 'Articles');
        
        // Assert BelongsToMany join table
        $this->assertBelongsToManyJoinTable(
            $this->Articles,
            'Tags',
            'articles_tags'
        );
    }
    
    public function testBehaviors(): void
    {
        // Assert has behavior
        $this->assertHasBehavior($this->Users, 'Timestamp');
        
        // Assert timestamp behavior working
        $user = $this->Users->newEntity(['username' => 'test']);
        $this->Users->save($user);
        $this->assertTimestampBehaviorWorking($user);
    }
    
    public function testEntityState(): void
    {
        $user = $this->Users->newEntity(['username' => 'test']);
        
        // Assert entity is new
        $this->assertEntityIsNew($user);
        
        $this->Users->save($user);
        
        // Assert entity is persisted
        $this->assertEntityIsPersisted($user);
        
        // Assert fields are dirty/clean
        $user->username = 'modified';
        $this->assertFieldsAreDirty($user, ['username']);
        $this->assertFieldsAreClean($user, ['email']);
    }
    
    public function testDatabase(): void
    {
        // Assert record exists
        $this->assertRecordExists($this->Users, [
            'username' => 'testuser',
        ]);
        
        // Assert record count
        $this->assertRecordCount($this->Users, 5, [
            'active' => 1,
        ]);
    }
}
```

### Available Methods

#### Validation
- `assertHasValidationError($entity, $field, ?$rule, $message)` - Assert has error
- `assertHasNoValidationError($entity, $field, $message)` - Assert no error
- `assertHasNoValidationErrors($entity, $message)` - Assert no errors
- `assertHasValidationErrors($entity, array $expectedErrors)` - Assert multiple
- `assertValidationErrorContains($entity, $field, $expectedText)` - Assert text

#### Associations
- `assertHasAssociation($table, $associationName, $message)` - Assert has assoc
- `assertAssociationType($table, $associationName, $expectedType)` - Assert type
- `assertAssociationTarget($table, $associationName, $expectedTable)` - Assert target
- `assertBelongsToManyJoinTable($table, $associationName, $expectedJoinTable)` - Assert join table
- `assertAssociationLoaded($entity, $associationName)` - Assert loaded

#### Behaviors
- `assertHasBehavior($table, $behaviorName, $message)` - Assert has behavior
- `assertDoesNotHaveBehavior($table, $behaviorName)` - Assert doesn't have
- `assertTimestampBehaviorWorking($entity)` - Assert timestamp works

#### Entity State
- `assertEntityIsNew($entity)` - Assert new
- `assertEntityIsPersisted($entity)` - Assert persisted
- `assertFieldsAreDirty($entity, array $fields)` - Assert dirty
- `assertFieldsAreClean($entity, array $fields)` - Assert clean
- `assertFieldsAreAccessible($entity, array $fields)` - Assert accessible

#### Database
- `assertRecordExists($table, array $conditions)` - Assert exists
- `assertRecordNotExists($table, array $conditions)` - Assert not exists
- `assertRecordCount($table, int $expected, array $conditions)` - Assert count

#### Collections
- `assertCollectionHasEntityWith(array $entities, $field, $value)` - Assert has entity
- `assertAllEntitiesHaveProperty(array $entities, $property)` - Assert all have property

#### JSON
- `assertJsonStructure(array $json, array $expectedKeys)` - Assert structure
- `assertJsonMatches(array $expected, array $actual, array $ignoreKeys)` - Assert matches

#### HTTP
- `assertResponseSuccess($response)` - Assert 2xx
- `assertResponseCode(int $expectedCode, $response)` - Assert status
- `assertResponseHasHeader(string $header, ?string $expectedValue, $response)` - Assert header

---

## 3. FixtureHelpersTrait

**File:** `app/tests/TestCase/Traits/FixtureHelpersTrait.php`

### Features

- ✅ Dynamic entity creation
- ✅ Test data factories
- ✅ Related data setup
- ✅ Test data cleanup
- ✅ Fixture data manipulation
- ✅ Batch operations

### Usage Examples

```php
<?php
use App\Test\TestCase\Traits\FixtureHelpersTrait;

class ArticlesControllerTest extends IntegrationTestCase
{
    use FixtureHelpersTrait;
    
    public function testCreateArticle(): void
    {
        // Create test user
        $user = $this->createTestUser([
            'username' => 'author',
        ]);
        
        // Create test article
        $article = $this->createTestArticle([
            'user_id' => $user->id,
            'title' => 'My Test Article',
        ]);
        
        // Test with article
        $this->assertNotNull($article->id);
    }
    
    public function testArticleWithRelations(): void
    {
        // Create article with all related data
        $data = $this->createArticleWithRelatedData([
            'user' => ['username' => 'author'],
            'article' => ['title' => 'Test'],
            'tags' => ['php', 'cakephp', 'testing'],
            'comments' => 5, // Create 5 comments
        ]);
        
        $this->assertNotNull($data['article']);
        $this->assertNotNull($data['user']);
        $this->assertCount(3, $data['tags']);
        $this->assertCount(5, $data['comments']);
    }
    
    public function testBatchCreation(): void
    {
        // Create 10 users
        $users = $this->createBatchTestEntities(
            'Users',
            10,
            ['role' => 'user'],
            function ($data, $i) {
                $data['username'] = "user_{$i}";
                $data['email'] = "user{$i}@example.com";
                return $data;
            }
        );
        
        $this->assertCount(10, $users);
    }
    
    public function testDataCleanup(): void
    {
        // Create test data
        $user = $this->createTestUser();
        
        // Run tests...
        
        // Cleanup
        $this->cleanupTestUsers('testuser_');
        
        // Assert cleaned up
        $this->assertTestDataCleanedUp('Users', [
            'username LIKE' => 'testuser_%',
        ]);
    }
    
    protected function tearDown(): void
    {
        // Cleanup all test data
        $this->cleanupTestUsers();
        $this->cleanupTestArticles();
        
        parent::tearDown();
    }
}
```

### Available Methods

#### Entity Creation
- `createTestUser(array $overrides)` - Create test user
- `createTestArticle(array $overrides)` - Create test article
- `createTestEntity(string $tableName, array $data, array $options)` - Create any entity
- `createMultipleTestEntities(string $tableName, array $records)` - Create multiple

#### Data Generation
- `generateUniqueEmail(string $prefix)` - Generate email
- `generateUniqueUsername(string $prefix)` - Generate username
- `generateRandomString(int $length, string $chars)` - Generate random string
- `generateTestDataForField($table, $field, bool $valid)` - Generate field data

#### Related Data
- `createArticleWithRelatedData(array $options)` - Create article + relations
- `createTestTags(string $articleId, array $tagNames)` - Create tags
- `createTestComments(string $articleId, string $userId, int $count)` - Create comments

#### Cleanup
- `cleanupTestData(string $tableName, array $conditions)` - Delete test data
- `cleanupTestUsers(string $prefix)` - Delete test users
- `cleanupTestArticles(string $prefix)` - Delete test articles
- `truncateTable(string $tableName)` - Delete all records

#### Fixture Manipulation
- `extractFixtureData($entity, array $excludeFields)` - Extract fixture data
- `generateFixtureRecord(string $tableName, array $sampleData)` - Generate fixture code

#### Batch Operations
- `createBatchTestEntities(string $tableName, int $count, array $template, ?callable $modifier)` - Batch create
- `assertTestDataCleanedUp(string $tableName, array $conditions)` - Assert cleanup

---

## Complete Example Test Class

```php
<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Test\TestCase\Traits\AssertionHelpersTrait;
use App\Test\TestCase\Traits\FixtureHelpersTrait;
use App\Test\TestCase\Traits\MockServicesTrait;
use Cake\TestSuite\TestCase;

class ArticlesTableTest extends TestCase
{
    use AssertionHelpersTrait;
    use FixtureHelpersTrait;
    use MockServicesTrait;
    
    protected $Articles;
    
    protected array $fixtures = [
        'app.Articles',
        'app.Users',
        'app.Tags',
        'app.Comments',
    ];
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->Articles = $this->getTableLocator()->get('Articles');
        
        // Use mock cache
        $this->useMockCache();
        
        // Freeze time for consistent tests
        $this->freezeTime('2025-01-01 12:00:00');
    }
    
    protected function tearDown(): void
    {
        // Cleanup test data
        $this->cleanupTestArticles();
        $this->cleanupTestUsers();
        
        // Unfreeze time
        $this->unfreezeTime();
        
        unset($this->Articles);
        parent::tearDown();
    }
    
    public function testValidationSuccess(): void
    {
        $user = $this->createTestUser();
        
        $article = $this->Articles->newEntity([
            'user_id' => $user->id,
            'title' => 'Valid Title',
            'body' => 'Valid body content',
        ]);
        
        $this->assertHasNoValidationErrors($article);
    }
    
    public function testValidationErrors(): void
    {
        $article = $this->Articles->newEntity([]);
        
        $this->assertHasValidationErrors($article, [
            'user_id' => '_required',
            'title' => '_required',
        ]);
    }
    
    public function testAssociations(): void
    {
        $this->assertHasAssociation($this->Articles, 'Users');
        $this->assertAssociationType($this->Articles, 'Users', 'BelongsTo');
        $this->assertAssociationTarget($this->Articles, 'Users', 'Users');
        
        $this->assertHasAssociation($this->Articles, 'Tags');
        $this->assertAssociationType($this->Articles, 'Tags', 'BelongsToMany');
        $this->assertBelongsToManyJoinTable($this->Articles, 'Tags', 'articles_tags');
    }
    
    public function testAiMetadataGeneration(): void
    {
        // Mock AI service
        $this->mockAiMetricsRecording();
        
        // Spy on queued jobs
        $jobs = $this->spyQueuedJobs();
        
        // Create article
        $data = $this->createArticleWithRelatedData([
            'article' => ['title' => 'Test Article'],
        ]);
        
        // Assert AI job was queued
        $this->assertJobWasQueued('App\\Job\\ArticleTagUpdateJob');
    }
    
    public function testPublishedArticlesOnly(): void
    {
        // Create published article
        $published = $this->createTestArticle([
            'is_published' => 1,
        ]);
        
        // Create unpublished article
        $unpublished = $this->createTestArticle([
            'is_published' => 0,
        ]);
        
        // Query published
        $results = $this->Articles->find()
            ->where(['is_published' => 1])
            ->all()
            ->toArray();
        
        // Assert only published returned
        $this->assertCollectionHasEntityWith($results, 'id', $published->id);
        $this->assertGreaterThan(0, count($results));
    }
}
```

---

## Best Practices

### 1. Use Multiple Traits Together
Combine traits for comprehensive testing:
```php
class MyTest extends TestCase
{
    use AssertionHelpersTrait;
    use FixtureHelpersTrait;
    use MockServicesTrait;
}
```

### 2. Clean Up Test Data
Always clean up in `tearDown()`:
```php
protected function tearDown(): void
{
    $this->cleanupTestUsers();
    $this->cleanupTestArticles();
    $this->cleanupTempTestDirs();
    
    parent::tearDown();
}
```

### 3. Mock External Services
Never call real external services in tests:
```php
public function testAiFeature(): void
{
    // Mock it!
    $this->mockAiMetricsRecording();
    $mockAi = $this->mockAnthropicService([
        ['response' => 'test'],
    ]);
}
```

### 4. Use Descriptive Assertions
Use custom assertions for clarity:
```php
// Good
$this->assertHasValidationError($user, 'email', 'email');

// Less clear
$this->assertNotEmpty($user->getError('email'));
```

### 5. Freeze Time for Consistency
Test time-dependent logic consistently:
```php
$this->freezeTime('2025-01-01 12:00:00');
// ... tests
$this->unfreezeTime();
```

---

## PHPUnit 10.x Compatibility

All traits are compatible with PHPUnit 10.x:

✅ Uses `willReturn()` instead of `returnValue()`
✅ Uses `willReturnOnConsecutiveCalls()` properly
✅ No deprecated mock methods
✅ Proper type hints
✅ Modern PHPUnit assertions

---

## Related Documentation

- [CakePHP 5.x Testing Guide](https://book.cakephp.org/5/en/development/testing.html)
- [PHPUnit 10.x Documentation](https://phpunit.de/documentation.html)
- [MODEL_TESTS_PROGRESS.md](./MODEL_TESTS_PROGRESS.md) - Overall testing progress
- [CONTINUOUS_TESTING_WORKFLOW.md](../CONTINUOUS_TESTING_WORKFLOW.md) - Watch mode testing

---

**Created:** October 7, 2025  
**Author:** AI Agent (Claude 4.5 Sonnet)  
**Project:** WillowCMS
