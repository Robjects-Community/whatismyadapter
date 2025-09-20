<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\ArticleImageGenerationJob;
use App\Service\Api\ImageGenerationService;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Interop\Queue\Processor;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * ArticleImageGenerationJob Test Case
 */
class ArticleImageGenerationJobTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Job\ArticleImageGenerationJob
     */
    protected ArticleImageGenerationJob $job;

    /**
     * Mock message
     *
     * @var \Cake\Queue\Job\Message|MockObject
     */
    protected Message|MockObject $mockMessage;

    /**
     * Mock image generation service
     *
     * @var \App\Service\Api\ImageGenerationService|MockObject
     */
    protected ImageGenerationService|MockObject $mockImageService;

    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Articles',
        'app.Images',
        'app.Users',
    ];

    /**
     * Articles table instance
     *
     * @var \App\Model\Table\ArticlesTable
     */
    protected $ArticlesTable;

    /**
     * Images table instance
     *
     * @var \App\Model\Table\ImagesTable
     */
    protected $ImagesTable;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ArticlesTable = TableRegistry::getTableLocator()->get('Articles');
        $this->ImagesTable = TableRegistry::getTableLocator()->get('Images');

        // Create mock image generation service
        $this->mockImageService = $this->createMock(ImageGenerationService::class);

        $this->job = new ArticleImageGenerationJob();
        $this->mockMessage = $this->createMock(Message::class);

        // Set the mock service on the job
        $reflection = new \ReflectionClass($this->job);
        $property = $reflection->getProperty('imageGenerationService');
        $property->setAccessible(true);
        $property->setValue($this->job, $this->mockImageService);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->job, $this->mockMessage, $this->mockImageService);
        parent::tearDown();
    }

    /**
     * Test successful job execution
     *
     * @return void
     */
    public function testExecuteSuccess(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article Without Image',
            'body' => 'This is a test article that needs an image generated.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        // Mock message arguments
        $this->mockMessage->expects($this->exactly(3))
            ->method('getArgument')
            ->willReturnCallback(function ($arg) use ($article) {
                return match ($arg) {
                    'id' => $article->id,
                    'title' => $article->title,
                    'body' => $article->body,
                    default => null
                };
            });

        // Mock successful image generation
        $this->mockImageService->expects($this->once())
            ->method('generateImageForArticle')
            ->willReturn([
                'success' => true,
                'imagePath' => 'files/Articles/ai_generated/test-image.png',
                'prompt' => 'A test image for the article',
                'provider' => 'openai',
                'metadata' => [
                    'generated_at' => date('Y-m-d H:i:s'),
                    'provider' => 'openai',
                    'model' => 'dall-e-3',
                ],
            ]);

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::ACK, $result);

        // Verify that an image was created and associated with the article
        $images = $this->ImagesTable->find()
            ->where(['article_id' => $article->id])
            ->toArray();

        $this->assertNotEmpty($images);
        $this->assertEquals(1, count($images));
        $this->assertStringContainsString('ai_generated', $images[0]->file_path);
    }

    /**
     * Test job execution with missing article ID
     *
     * @return void
     */
    public function testExecuteWithMissingArticleId(): void
    {
        $this->mockMessage->expects($this->once())
            ->method('getArgument')
            ->with('id')
            ->willReturn(null);

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test job execution with non-existent article
     *
     * @return void
     */
    public function testExecuteWithNonExistentArticle(): void
    {
        $nonExistentId = '99999999-9999-9999-9999-999999999999';

        $this->mockMessage->expects($this->once())
            ->method('getArgument')
            ->with('id')
            ->willReturn($nonExistentId);

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }

    /**
     * Test job execution when article already has an image
     *
     * @return void
     */
    public function testExecuteWithArticleAlreadyHasImage(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article With Image',
            'body' => 'This article already has an image.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        // Create an image for the article
        $image = $this->ImagesTable->newEntity([
            'article_id' => $article->id,
            'file_path' => 'files/Articles/existing-image.jpg',
            'title' => 'Existing Image',
            'alt_text' => 'An existing image',
        ]);
        $this->ImagesTable->save($image);

        $this->mockMessage->expects($this->once())
            ->method('getArgument')
            ->with('id')
            ->willReturn($article->id);

        // Image generation service should not be called
        $this->mockImageService->expects($this->never())
            ->method('generateImageForArticle');

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::ACK, $result);
    }

    /**
     * Test job execution with image generation service failure
     *
     * @return void
     */
    public function testExecuteWithImageGenerationFailure(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article Image Generation Failure',
            'body' => 'This article will fail to generate an image.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        $this->mockMessage->expects($this->exactly(3))
            ->method('getArgument')
            ->willReturnCallback(function ($arg) use ($article) {
                return match ($arg) {
                    'id' => $article->id,
                    'title' => $article->title,
                    'body' => $article->body,
                    default => null
                };
            });

        // Mock failed image generation
        $this->mockImageService->expects($this->once())
            ->method('generateImageForArticle')
            ->willReturn([
                'success' => false,
                'error' => 'API rate limit exceeded',
            ]);

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);

        // Verify no image was created
        $images = $this->ImagesTable->find()
            ->where(['article_id' => $article->id])
            ->toArray();

        $this->assertEmpty($images);
    }

    /**
     * Test job execution with unpublished article
     *
     * @return void
     */
    public function testExecuteWithUnpublishedArticle(): void
    {
        // Create an unpublished test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Unpublished Test Article',
            'body' => 'This article is not published.',
            'kind' => 'article',
            'is_published' => false,
        ]);
        $this->ArticlesTable->save($article);

        $this->mockMessage->expects($this->once())
            ->method('getArgument')
            ->with('id')
            ->willReturn($article->id);

        // Image generation service should not be called for unpublished articles
        $this->mockImageService->expects($this->never())
            ->method('generateImageForArticle');

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::ACK, $result);
    }

    /**
     * Test job execution with non-article type
     *
     * @return void
     */
    public function testExecuteWithNonArticleType(): void
    {
        // Create a page instead of an article
        $page = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Page',
            'body' => 'This is a page, not an article.',
            'kind' => 'page',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($page);

        $this->mockMessage->expects($this->once())
            ->method('getArgument')
            ->with('id')
            ->willReturn($page->id);

        // Image generation service should not be called for pages
        $this->mockImageService->expects($this->never())
            ->method('generateImageForArticle');

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::ACK, $result);
    }

    /**
     * Test metadata storage when image is successfully generated
     *
     * @return void
     */
    public function testMetadataStorage(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article Metadata',
            'body' => 'This article will have metadata stored.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        $this->mockMessage->expects($this->exactly(3))
            ->method('getArgument')
            ->willReturnCallback(function ($arg) use ($article) {
                return match ($arg) {
                    'id' => $article->id,
                    'title' => $article->title,
                    'body' => $article->body,
                    default => null
                };
            });

        $testMetadata = [
            'generated_at' => date('Y-m-d H:i:s'),
            'provider' => 'openai',
            'model' => 'dall-e-3',
            'prompt' => 'A test prompt for the image',
            'size' => '1024x1024',
            'quality' => 'standard',
        ];

        // Mock successful image generation with metadata
        $this->mockImageService->expects($this->once())
            ->method('generateImageForArticle')
            ->willReturn([
                'success' => true,
                'imagePath' => 'files/Articles/ai_generated/test-image-metadata.png',
                'prompt' => 'A test prompt for the image',
                'provider' => 'openai',
                'metadata' => $testMetadata,
            ]);

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::ACK, $result);

        // Verify image was created with proper metadata
        $image = $this->ImagesTable->find()
            ->where(['article_id' => $article->id])
            ->first();

        $this->assertNotNull($image);
        $this->assertEquals('A test prompt for the image', $image->alt_text);
        
        // Check if metadata was stored (assuming there's a metadata field)
        if (isset($image->metadata)) {
            $storedMetadata = json_decode($image->metadata, true);
            $this->assertEquals('openai', $storedMetadata['provider']);
            $this->assertEquals('dall-e-3', $storedMetadata['model']);
        }
    }

    /**
     * Test job retry mechanism
     *
     * @return void
     */
    public function testJobRetryLogic(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article Retry',
            'body' => 'This article will test retry logic.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        $this->mockMessage->expects($this->exactly(3))
            ->method('getArgument')
            ->willReturnCallback(function ($arg) use ($article) {
                return match ($arg) {
                    'id' => $article->id,
                    'title' => $article->title,
                    'body' => $article->body,
                    default => null
                };
            });

        // Mock temporary failure that should be retried
        $this->mockImageService->expects($this->once())
            ->method('generateImageForArticle')
            ->willReturn([
                'success' => false,
                'error' => 'Temporary service unavailable',
                'retryable' => true,
            ]);

        $result = $this->job->execute($this->mockMessage);

        // Should return REQUEUE for retryable failures
        $this->assertEquals(Processor::REQUEUE, $result);
    }

    /**
     * Test job with exception handling
     *
     * @return void
     */
    public function testJobWithException(): void
    {
        // Create a test article
        $article = $this->ArticlesTable->newEntity([
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article Exception',
            'body' => 'This article will cause an exception.',
            'kind' => 'article',
            'is_published' => true,
        ]);
        $this->ArticlesTable->save($article);

        $this->mockMessage->expects($this->exactly(3))
            ->method('getArgument')
            ->willReturnCallback(function ($arg) use ($article) {
                return match ($arg) {
                    'id' => $article->id,
                    'title' => $article->title,
                    'body' => $article->body,
                    default => null
                };
            });

        // Mock service throwing exception
        $this->mockImageService->expects($this->once())
            ->method('generateImageForArticle')
            ->willThrowException(new \Exception('Service unavailable'));

        $result = $this->job->execute($this->mockMessage);

        $this->assertEquals(Processor::REJECT, $result);
    }
}