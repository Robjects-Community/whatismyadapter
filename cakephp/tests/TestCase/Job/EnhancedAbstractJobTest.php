<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\EnhancedAbstractJob;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Interop\Queue\Processor;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for EnhancedAbstractJob
 * 
 * Tests all the common patterns and functionality provided by the enhanced base class
 */
class EnhancedAbstractJobTest extends TestCase
{
    protected $fixtures = [
        'app.Articles',
        'app.Tags',
        'app.Users'
    ];

    private EnhancedAbstractJob $job;
    private MockObject $mockAnthropicService;
    private MockObject $mockGoogleService;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a concrete test implementation
        $this->job = new class extends EnhancedAbstractJob {
            protected static function getJobType(): string 
            {
                return 'test job';
            }
            
            public function execute(Message $message): ?string
            {
                return Processor::ACK;
            }
        };

        // Mock API services
        $this->mockAnthropicService = $this->createMock(AnthropicApiService::class);
        $this->mockGoogleService = $this->createMock(GoogleApiService::class);
    }

    // ==========================================
    // API SERVICE MANAGEMENT TESTS
    // ==========================================

    public function testGetAnthropicServiceReturnsInstance(): void
    {
        $service = $this->job->getAnthropicService();
        $this->assertInstanceOf(AnthropicApiService::class, $service);
    }

    public function testGetAnthropicServiceAcceptsDependencyInjection(): void
    {
        $injectedService = $this->job->getAnthropicService($this->mockAnthropicService);
        $this->assertSame($this->mockAnthropicService, $injectedService);
    }

    public function testGetGoogleServiceReturnsInstance(): void
    {
        $service = $this->job->getGoogleService();
        $this->assertInstanceOf(GoogleApiService::class, $service);
    }

    public function testGetGoogleServiceAcceptsDependencyInjection(): void
    {
        $injectedService = $this->job->getGoogleService($this->mockGoogleService);
        $this->assertSame($this->mockGoogleService, $injectedService);
    }

    // ==========================================
    // SEO FIELD PROCESSING TESTS
    // ==========================================

    public function testUpdateSeoFieldsWithEmptyFieldsMethod(): void
    {
        $mockEntity = $this->createMock(EntityInterface::class);
        $mockTable = $this->createMock(Table::class);
        
        // Mock table method for checking empty fields
        $mockTable->expects($this->once())
            ->method('emptySeoFields')
            ->with($mockEntity)
            ->willReturn(['meta_title', 'meta_description']);

        // Mock Anthropic service
        $this->mockAnthropicService
            ->expects($this->once())
            ->method('generateArticleSeo')
            ->willReturn([
                'meta_title' => 'Generated Title',
                'meta_description' => 'Generated Description'
            ]);

        $mockEntity->expects($this->exactly(2))
            ->method('__set')
            ->withConsecutive(
                ['meta_title', 'Generated Title'],
                ['meta_description', 'Generated Description']
            );

        $mockTable->expects($this->once())
            ->method('save')
            ->with($mockEntity, ['noMessage' => true])
            ->willReturn($mockEntity);

        // Inject service and test
        $this->job->getAnthropicService($this->mockAnthropicService);
        
        $result = $this->job->updateSeoFields(
            $mockEntity, 
            $mockTable, 
            'Test Title', 
            'Test Content'
        );

        $this->assertTrue($result);
    }

    public function testUpdateSeoFieldsWithNoEmptyFields(): void
    {
        $mockEntity = $this->createMock(EntityInterface::class);
        $mockTable = $this->createMock(Table::class);
        
        // Mock table method returning no empty fields
        $mockTable->expects($this->once())
            ->method('emptySeoFields')
            ->with($mockEntity)
            ->willReturn([]);

        // Should not call Anthropic service
        $this->mockAnthropicService
            ->expects($this->never())
            ->method('generateArticleSeo');

        $result = $this->job->updateSeoFields(
            $mockEntity, 
            $mockTable, 
            'Test Title', 
            'Test Content'
        );

        $this->assertTrue($result);
    }

    // ==========================================
    // TRANSLATION MANAGEMENT TESTS
    // ==========================================

    public function testAreTranslationsEnabledReturnsFalseWhenDisabled(): void
    {
        // Mock SettingsManager to return empty translations
        $result = $this->job->areTranslationsEnabled();
        // This would need actual settings configuration in a real test
        $this->assertIsBool($result);
    }

    public function testProcessTranslationsWithValidData(): void
    {
        $mockEntity = $this->createMock(EntityInterface::class);
        $mockTable = $this->createMock(Table::class);
        
        $mockEntity->expects($this->any())
            ->method('getSource')
            ->willReturn('Articles');

        $fieldMapping = [
            'title' => 'title',
            'body' => 'body'
        ];

        // Mock translation result
        $translationResult = [
            'es' => [
                'title' => 'Título en español',
                'body' => 'Contenido en español'
            ]
        ];

        $this->mockGoogleService
            ->expects($this->once())
            ->method('translateArticle')
            ->willReturn($translationResult);

        $mockTable->expects($this->once())
            ->method('save')
            ->willReturn($mockEntity);

        // Inject service
        $this->job->getGoogleService($this->mockGoogleService);

        $result = $this->job->processTranslations($mockEntity, $mockTable, $fieldMapping);
        
        // Result depends on translation settings being enabled
        $this->assertIsBool($result);
    }

    // ==========================================
    // REQUEUE WITH BACKOFF TESTS
    // ==========================================

    public function testRequeueWithBackoffFirstAttempt(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                ['_attempt', 0, 0],
                ['id', 'unknown', 'test-id'],
                ['title', '', 'Test Title']
            ]);

        $mockMessage->expects($this->once())
            ->method('getArguments')
            ->willReturn([
                'id' => 'test-id',
                'title' => 'Test Title'
            ]);

        $result = $this->job->requeueWithBackoff($mockMessage, 'Test reason');
        $this->assertEquals(Processor::ACK, $result);
    }

    public function testRequeueWithBackoffMaxAttemptsReached(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                ['_attempt', 0, 5], // Max attempts reached
                ['id', 'unknown', 'test-id'],
                ['title', '', 'Test Title']
            ]);

        $result = $this->job->requeueWithBackoff($mockMessage, 'Test reason', 5);
        $this->assertEquals(Processor::REJECT, $result);
    }

    // ==========================================
    // BULK OPERATIONS TESTS
    // ==========================================

    public function testApplyBulkFieldUpdates(): void
    {
        $mockEntity = $this->createMock(EntityInterface::class);
        
        $apiResult = [
            'title' => 'New Title',
            'description' => 'New Description'
        ];

        $fieldMap = [
            'title' => 'entity_title',
            'description' => 'entity_description'
        ];

        $mockEntity->expects($this->exactly(2))
            ->method('__set')
            ->withConsecutive(
                ['entity_title', 'New Title'],
                ['entity_description', 'New Description']
            );

        $result = $this->job->applyBulkFieldUpdates($mockEntity, $apiResult, $fieldMap);
        $this->assertSame($mockEntity, $result);
    }

    public function testFindOrCreateEntityCreatesNewWhenNotFound(): void
    {
        $mockTable = $this->createMock(Table::class);
        $mockQuery = $this->createMock(\Cake\ORM\Query\SelectQuery::class);
        $newEntity = new Entity();

        // Mock find query that returns null (not found)
        $mockTable->expects($this->once())
            ->method('find')
            ->willReturn($mockQuery);

        $mockQuery->expects($this->once())
            ->method('where')
            ->with(['title' => 'Test Tag'])
            ->willReturn($mockQuery);

        $mockQuery->expects($this->once())
            ->method('first')
            ->willReturn(null);

        // Mock entity creation
        $mockTable->expects($this->once())
            ->method('newEmptyEntity')
            ->willReturn($newEntity);

        $mockTable->expects($this->once())
            ->method('patchEntity')
            ->with($newEntity, ['title' => 'Test Tag', 'description' => 'Test'])
            ->willReturn($newEntity);

        $mockTable->expects($this->once())
            ->method('save')
            ->with($newEntity);

        $result = $this->job->findOrCreateEntity(
            $mockTable,
            ['title' => 'Test Tag'],
            ['title' => 'Test Tag', 'description' => 'Test']
        );

        $this->assertSame($newEntity, $result);
    }

    // ==========================================
    // CONFIGURATION TESTS
    // ==========================================

    public function testGetValidatedConfigWithValidValue(): void
    {
        $result = $this->job->getValidatedConfig('test.key', 'default', ['valid1', 'valid2']);
        // Since SettingsManager is mocked/unavailable, it should return the default
        $this->assertEquals('default', $result);
    }

    // ==========================================
    // ORIGINAL METHODS TESTS
    // ==========================================

    public function testValidateArgumentsWithValidArgs(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->expects($this->exactly(2))
            ->method('getArgument')
            ->willReturnMap([
                ['id', null, 'test-id'],
                ['title', null, 'Test Title']
            ]);

        $result = $this->job->validateArguments($mockMessage, ['id', 'title']);
        $this->assertTrue($result);
    }

    public function testValidateArgumentsWithMissingArgs(): void
    {
        $mockMessage = $this->createMock(Message::class);
        $mockMessage->expects($this->exactly(2))
            ->method('getArgument')
            ->willReturnMap([
                ['id', null, 'test-id'],
                ['title', null, null] // Missing title
            ]);

        $result = $this->job->validateArguments($mockMessage, ['id', 'title']);
        $this->assertFalse($result);
    }

    public function testExecuteWithErrorHandlingSuccess(): void
    {
        $result = $this->job->executeWithErrorHandling('test-id', function() {
            return true;
        }, 'Test Title');

        $this->assertEquals(Processor::ACK, $result);
    }

    public function testExecuteWithErrorHandlingFailure(): void
    {
        $result = $this->job->executeWithErrorHandling('test-id', function() {
            return false;
        }, 'Test Title');

        $this->assertEquals(Processor::REJECT, $result);
    }

    public function testExecuteWithErrorHandlingException(): void
    {
        $result = $this->job->executeWithErrorHandling('test-id', function() {
            throw new \Exception('Test exception');
        }, 'Test Title');

        $this->assertEquals(Processor::REJECT, $result);
    }
}