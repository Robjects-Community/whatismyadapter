<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use Cake\Cache\Cache;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Exception;
use Interop\Queue\Processor;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * AbstractJob Test Case
 *
 * Tests the base functionality provided by AbstractJob including logging,
 * error handling, and common operations.
 */
class AbstractJobTest extends TestCase
{
    private TestableJob $job;
    private Message|MockObject $mockMessage;
    protected Table $UsersTable;

    public function setUp(): void
    {
        parent::setUp();

        $this->job = new TestableJob();
        $this->mockMessage = $this->createMock(Message::class);
        $this->UsersTable = TableRegistry::getTableLocator()->get('Users');

        // Clear any existing cache
        Cache::clear('content');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Cache::clear('content');
    }

    /**
     * Test executeWithErrorHandling with successful operation
     */
    public function testExecuteWithErrorHandlingSuccess(): void
    {
        $result = $this->job->testExecuteWithErrorHandling('test-id', function () {
            return true;
        }, 'Test Title');

        $this->assertEquals(Processor::ACK, $result);
    }

    /**
     * Test executeWithErrorHandling with operation returning false
     */
    public function testExecuteWithErrorHandlingOperationReturnsFalse(): void
    {
        $result = $this->job->testExecuteWithErrorHandling('test-id', function () {
            return false;
        });

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test executeWithErrorHandling with operation returning null
     */
    public function testExecuteWithErrorHandlingOperationReturnsNull(): void
    {
        $result = $this->job->testExecuteWithErrorHandling('test-id', function () {
            return null;
        });

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test executeWithErrorHandling with exception thrown
     */
    public function testExecuteWithErrorHandlingException(): void
    {
        $result = $this->job->testExecuteWithErrorHandling('test-id', function () {
            throw new Exception('Test exception');
        });

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test executeWithEntitySave with successful entity save
     */
    public function testExecuteWithEntitySaveSuccess(): void
    {
        // Create a new user entity for testing
        $user = $this->UsersTable->newEntity([
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => 'testpassword123',
            'confirm_password' => 'testpassword123',
            'first_name' => 'Test',
            'last_name' => 'User',
            'active' => true,
        ]);

        // Test successful entity save
        $result = $this->job->testExecuteWithEntitySave('test-id', function () use ($user) {
            return $user;
        }, 'Test User Save');

        $this->assertEquals(Processor::ACK, $result);

        // Verify the user was actually saved
        $savedUser = $this->UsersTable->find()
            ->where(['username' => $user->username])
            ->first();
        $this->assertNotNull($savedUser);
        $this->assertEquals($user->email, $savedUser->email);

        // Clean up
        $this->UsersTable->delete($savedUser);
    }

    /**
     * Test executeWithEntitySave with failed entity save
     */
    public function testExecuteWithEntitySaveFailure(): void
    {
        // Create an invalid user entity (missing required username)
        $user = $this->UsersTable->newEntity([
            'email' => 'invalid@example.com',
            // Missing required 'username' field will cause validation failure
        ]);

        // Test failed entity save
        $result = $this->job->testExecuteWithEntitySave('test-id', function () use ($user) {
            return $user;
        }, 'Test User Save Failure');

        $this->assertEquals(Processor::REJECT, $result);

        // Verify the user was not saved
        $savedUser = $this->UsersTable->find()
            ->where(['email' => 'invalid@example.com'])
            ->first();
        $this->assertNull($savedUser);
    }

    /**
     * Test executeWithEntitySave with non-entity return value
     */
    public function testExecuteWithEntitySaveNonEntity(): void
    {
        $result = $this->job->testExecuteWithEntitySave('test-id', function () {
            return true;
        });

        $this->assertEquals(Processor::ACK, $result);
    }

    /**
     * Test validateArguments with all required arguments present
     */
    public function testValidateArgumentsSuccess(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(function ($arg) {
                return match ($arg) {
                    'arg1' => 'value1',
                    'arg2' => 'value2',
                    default => null
                };
            });

        $result = $this->job->testValidateArguments($this->mockMessage, ['arg1', 'arg2']);

        $this->assertTrue($result);
    }

    /**
     * Test validateArguments with missing required argument
     */
    public function testValidateArgumentsMissing(): void
    {
        $this->mockMessage->method('getArgument')
            ->willReturnCallback(function ($arg) {
                return match ($arg) {
                    'arg1' => 'value1',
                    'arg2' => null,
                    default => null
                };
            });

        $result = $this->job->testValidateArguments($this->mockMessage, ['arg1', 'arg2']);

        $this->assertFalse($result);
    }

    /**
     * Test clearContentCache clears the content cache
     */
    public function testClearContentCache(): void
    {
        // Set something in the cache first
        Cache::write('test_key', 'test_value', 'content');
        $this->assertEquals('test_value', Cache::read('test_key', 'content'));

        // Clear cache and verify it's gone
        $this->job->testClearContentCache();
        $this->assertNull(Cache::read('test_key', 'content'));
    }

    /**
     * Test getTable returns a table instance
     */
    public function testGetTable(): void
    {
        $table = $this->job->testGetTable('Articles');

        $this->assertInstanceOf(Table::class, $table);
        $this->assertEquals('articles', $table->getTable());
    }

    /**
     * Test static properties are set correctly
     */
    public function testStaticProperties(): void
    {
        $this->assertEquals(3, TestableJob::$maxAttempts);
        $this->assertTrue(TestableJob::$shouldBeUnique);
    }

    /**
     * Test getJobType returns correct value
     */
    public function testGetJobType(): void
    {
        $this->assertEquals('test job', TestableJob::getTestJobType());
    }

    /**
     * Test logging methods don't throw exceptions
     */
    public function testLoggingMethods(): void
    {
        // These should not throw exceptions
        $this->job->testLogJobStart('test-id', 'Test Title');
        $this->job->testLogJobSuccess('test-id', 'Test Title');
        $this->job->testLogJobError('test-id', 'Test error', 'Test Title');

        // If we get here without exceptions, the test passes
        $this->assertTrue(true);
    }
}
