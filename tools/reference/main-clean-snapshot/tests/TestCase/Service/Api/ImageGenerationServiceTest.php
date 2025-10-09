<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\ImageGenerationService;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Log\Log;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * ImageGenerationService Test Case
 */
class ImageGenerationServiceTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Service\Api\ImageGenerationService
     */
    protected ImageGenerationService $ImageGenerationService;

    /**
     * Mock HTTP client
     *
     * @var \Cake\Http\Client|MockObject
     */
    protected Client|MockObject $mockClient;

    /**
     * Mock configuration
     *
     * @var array
     */
    protected array $mockConfig;

    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Articles',
        'app.Images',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear any existing cache
        Cache::clear('image_generation');

        // Mock HTTP client
        $this->mockClient = $this->createMock(Client::class);

        // Mock configuration
        $this->mockConfig = [
            'enabled' => true,
            'primaryProvider' => 'openai',
            'fallbackProvider' => 'unsplash',
            'apiKeys' => [
                'openai' => 'test-openai-key',
                'anthropic' => 'test-anthropic-key',
                'unsplash' => 'test-unsplash-key',
            ],
            'model' => 'dall-e-3',
            'size' => '1024x1024',
            'quality' => 'standard',
            'rateLimits' => [
                'perMinute' => 5,
                'perHour' => 50,
                'perDay' => 200,
            ],
            'contentFilter' => 'moderate',
            'maxRetries' => 3,
            'timeout' => 60,
            'storagePath' => 'files/Articles/ai_generated/',
            'debugLogging' => false,
        ];

        $this->ImageGenerationService = new ImageGenerationService($this->mockConfig, $this->mockClient);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageGenerationService, $this->mockClient);
        Cache::clear('image_generation');
        parent::tearDown();
    }

    /**
     * Test service instantiation
     *
     * @return void
     */
    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(ImageGenerationService::class, $this->ImageGenerationService);
    }

    /**
     * Test generateImageForArticle with OpenAI success
     *
     * @return void
     */
    public function testGenerateImageForArticleOpenAISuccess(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article About Technology',
            'body' => 'This is a test article about artificial intelligence and machine learning.',
            'summary' => 'AI and ML overview',
        ];

        // Mock successful OpenAI response
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('isSuccess')->willReturn(true);
        $mockResponse->method('getJson')->willReturn([
            'data' => [
                [
                    'url' => 'https://example.com/generated-image.png',
                    'revised_prompt' => 'A futuristic representation of artificial intelligence and machine learning',
                ]
            ]
        ]);

        // Mock image download response
        $mockImageResponse = $this->createMock(Response::class);
        $mockImageResponse->method('isSuccess')->willReturn(true);
        $mockImageResponse->method('getBody')->willReturn('fake-image-data');
        $mockImageResponse->method('getHeaderLine')->with('content-type')->willReturn('image/png');

        $this->mockClient->expects($this->exactly(2))
            ->method('post')
            ->willReturnOnConsecutiveCalls($mockResponse, $mockImageResponse);

        $result = $this->ImageGenerationService->generateImageForArticle($articleData);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['imagePath']);
        $this->assertNotEmpty($result['prompt']);
        $this->assertEquals('openai', $result['provider']);
        $this->assertArrayHasKey('metadata', $result);
    }

    /**
     * Test generateImageForArticle with OpenAI failure and fallback
     *
     * @return void
     */
    public function testGenerateImageForArticleWithFallback(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article About Nature',
            'body' => 'This article discusses forests and wildlife.',
        ];

        // Mock failed OpenAI response
        $mockFailedResponse = $this->createMock(Response::class);
        $mockFailedResponse->method('isSuccess')->willReturn(false);
        $mockFailedResponse->method('getStatusCode')->willReturn(429);

        // Mock successful Unsplash response
        $mockUnsplashResponse = $this->createMock(Response::class);
        $mockUnsplashResponse->method('isSuccess')->willReturn(true);
        $mockUnsplashResponse->method('getJson')->willReturn([
            'results' => [
                [
                    'urls' => [
                        'regular' => 'https://images.unsplash.com/photo-123?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80'
                    ],
                    'description' => 'Beautiful forest landscape',
                    'user' => [
                        'name' => 'Test Photographer'
                    ]
                ]
            ]
        ]);

        // Mock image download
        $mockImageResponse = $this->createMock(Response::class);
        $mockImageResponse->method('isSuccess')->willReturn(true);
        $mockImageResponse->method('getBody')->willReturn('fake-image-data');
        $mockImageResponse->method('getHeaderLine')->with('content-type')->willReturn('image/jpeg');

        $this->mockClient->expects($this->exactly(3))
            ->method($this->anything())
            ->willReturnOnConsecutiveCalls($mockFailedResponse, $mockUnsplashResponse, $mockImageResponse);

        $result = $this->ImageGenerationService->generateImageForArticle($articleData);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['imagePath']);
        $this->assertEquals('unsplash', $result['provider']);
        $this->assertArrayHasKey('fallbackReason', $result);
    }

    /**
     * Test rate limiting functionality
     *
     * @return void
     */
    public function testRateLimiting(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article',
            'body' => 'Test content',
        ];

        // Set cache to simulate rate limit exceeded
        Cache::write('image_gen_rate_limit_minute', 10, 'image_generation');

        $result = $this->ImageGenerationService->generateImageForArticle($articleData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('rate limit', $result['error']);
    }

    /**
     * Test content filtering
     *
     * @return void
     */
    public function testContentFiltering(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Inappropriate Content Test',
            'body' => 'This contains harmful content that should be filtered out.',
        ];

        // The service should filter inappropriate content
        $result = $this->ImageGenerationService->generateImageForArticle($articleData);

        // Depending on implementation, this might return filtered content or reject
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * Test prompt building functionality
     *
     * @return void
     */
    public function testPromptBuilding(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Understanding Quantum Physics',
            'body' => 'Quantum physics explores the behavior of matter and energy at the molecular, atomic, nuclear, and even smaller microscopic levels.',
            'summary' => 'An introduction to quantum physics concepts',
        ];

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->ImageGenerationService);
        $method = $reflection->getMethod('buildPrompt');
        $method->setAccessible(true);

        $prompt = $method->invoke($this->ImageGenerationService, $articleData);

        $this->assertIsString($prompt);
        $this->assertStringContainsString('quantum', strtolower($prompt));
        $this->assertStringContainsString('physics', strtolower($prompt));
        $this->assertLessThan(500, strlen($prompt)); // Ensure prompt isn't too long
    }

    /**
     * Test image sanitization
     *
     * @return void
     */
    public function testImageSanitization(): void
    {
        // Create a test image with metadata
        $testImageData = 'fake-image-data-with-metadata';

        $reflection = new \ReflectionClass($this->ImageGenerationService);
        $method = $reflection->getMethod('sanitizeImage');
        $method->setAccessible(true);

        $sanitized = $method->invoke($this->ImageGenerationService, $testImageData);

        $this->assertIsString($sanitized);
        // In a real implementation, this would remove EXIF data
    }

    /**
     * Test error handling with invalid configuration
     *
     * @return void
     */
    public function testInvalidConfiguration(): void
    {
        $invalidConfig = [];
        
        $this->expectException(\InvalidArgumentException::class);
        new ImageGenerationService($invalidConfig);
    }

    /**
     * Test retry mechanism
     *
     * @return void
     */
    public function testRetryMechanism(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article',
            'body' => 'Test content for retry mechanism',
        ];

        // Mock three failed responses then success
        $mockFailedResponse = $this->createMock(Response::class);
        $mockFailedResponse->method('isSuccess')->willReturn(false);
        $mockFailedResponse->method('getStatusCode')->willReturn(500);

        $mockSuccessResponse = $this->createMock(Response::class);
        $mockSuccessResponse->method('isSuccess')->willReturn(true);
        $mockSuccessResponse->method('getJson')->willReturn([
            'data' => [
                ['url' => 'https://example.com/generated-image.png']
            ]
        ]);

        $mockImageResponse = $this->createMock(Response::class);
        $mockImageResponse->method('isSuccess')->willReturn(true);
        $mockImageResponse->method('getBody')->willReturn('fake-image-data');
        $mockImageResponse->method('getHeaderLine')->with('content-type')->willReturn('image/png');

        $this->mockClient->expects($this->exactly(4))
            ->method('post')
            ->willReturnOnConsecutiveCalls(
                $mockFailedResponse,
                $mockFailedResponse,
                $mockSuccessResponse,
                $mockImageResponse
            );

        $result = $this->ImageGenerationService->generateImageForArticle($articleData);

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['retryCount']); // Should have retried twice before success
    }

    /**
     * Test metadata generation
     *
     * @return void
     */
    public function testMetadataGeneration(): void
    {
        $articleData = [
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'title' => 'Test Article',
            'body' => 'Test content',
        ];

        $reflection = new \ReflectionClass($this->ImageGenerationService);
        $method = $reflection->getMethod('generateMetadata');
        $method->setAccessible(true);

        $metadata = $method->invoke($this->ImageGenerationService, $articleData, 'openai', 'test prompt');

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('generated_at', $metadata);
        $this->assertArrayHasKey('provider', $metadata);
        $this->assertArrayHasKey('prompt', $metadata);
        $this->assertArrayHasKey('article_id', $metadata);
        $this->assertEquals('openai', $metadata['provider']);
        $this->assertEquals($articleData['id'], $metadata['article_id']);
    }

    /**
     * Test statistics gathering
     *
     * @return void
     */
    public function testStatisticsGathering(): void
    {
        // Generate some test data
        Cache::write('image_gen_stats_total', 10, 'image_generation');
        Cache::write('image_gen_stats_success', 8, 'image_generation');
        Cache::write('image_gen_stats_failed', 2, 'image_generation');

        $reflection = new \ReflectionClass($this->ImageGenerationService);
        $method = $reflection->getMethod('getStatistics');
        $method->setAccessible(true);

        $stats = $method->invoke($this->ImageGenerationService);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_generated', $stats);
        $this->assertArrayHasKey('success_count', $stats);
        $this->assertArrayHasKey('failure_count', $stats);
        $this->assertArrayHasKey('success_rate', $stats);
    }
}