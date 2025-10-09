<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Anthropic\ArticleTagsGenerator;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use ReflectionClass;

/**
 * ArticleTagsGeneratorTest
 *
 * Tests the ArticleTagsGenerator functionality without making actual API calls.
 */
class ArticleTagsGeneratorTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Aiprompts',
    ];

    private AnthropicApiService $mockApiService;
    private AipromptsTable $aipromptsTable;
    private ArticleTagsGenerator $generator;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->mockApiService = $this->createMock(AnthropicApiService::class);
        $this->aipromptsTable = $this->getTableLocator()->get('Aiprompts');
        $this->generator = new ArticleTagsGenerator($this->mockApiService, $this->aipromptsTable);
    }

    /**
     * Test generateArticleTags with successful response
     */
    public function testGenerateArticleTagsSuccess(): void
    {
        // Create test prompt data
        $promptData = [
            'task_type' => 'article_tag_generation',
            'system_prompt' => 'Generate tags for the article',
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        // Mock API response
        $mockResponse = $this->createMock(Response::class);
        $apiResponse = ['tags' => 'technology, programming, web development'];

        $this->mockApiService->expects($this->once())
            ->method('sendRequest')
            ->willReturn($mockResponse);

        $this->mockApiService->expects($this->once())
            ->method('parseResponse')
            ->with($mockResponse)
            ->willReturn($apiResponse);

        // Test data
        $allTags = ['existing-tag-1', 'existing-tag-2'];
        $title = 'Test Article Title';
        $body = 'This is a test article about technology and programming.';

        $result = $this->generator->generateArticleTags($allTags, $title, $body);

        $this->assertEquals($apiResponse, $result);
    }

    /**
     * Test generateArticleTags with missing response key
     */
    public function testGenerateArticleTagsWithMissingKey(): void
    {
        // Create test prompt data
        $promptData = [
            'task_type' => 'article_tag_generation',
            'system_prompt' => 'Generate tags for the article',
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        // Mock API response missing expected key
        $mockResponse = $this->createMock(Response::class);
        $apiResponse = ['unexpected_key' => 'some value'];

        $this->mockApiService->expects($this->once())
            ->method('sendRequest')
            ->willReturn($mockResponse);

        $this->mockApiService->expects($this->once())
            ->method('parseResponse')
            ->with($mockResponse)
            ->willReturn($apiResponse);

        $allTags = ['existing-tag-1'];
        $title = 'Test Title';
        $body = 'Test body content.';

        $result = $this->generator->generateArticleTags($allTags, $title, $body);

        // Should add missing 'tags' key with empty string
        $expected = [
            'unexpected_key' => 'some value',
            'tags' => '',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test generateArticleTags throws exception for unknown task
     */
    public function testGenerateArticleTagsThrowsExceptionForUnknownTask(): void
    {
        // Don't create the prompt data, so it will be "unknown"

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown task: article_tag_generation');

        $allTags = ['tag1', 'tag2'];
        $title = 'Test Title';
        $body = 'Test body content.';

        $this->generator->generateArticleTags($allTags, $title, $body);
    }

    /**
     * Test that the generator uses the correct expected keys
     */
    public function testGetExpectedKeys(): void
    {
        $reflection = new ReflectionClass($this->generator);
        $method = $reflection->getMethod('getExpectedKeys');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator);

        $this->assertEquals(['tags'], $result);
    }

    /**
     * Test that the generator has the correct logger name
     */
    public function testGetLoggerName(): void
    {
        $reflection = new ReflectionClass($this->generator);
        $method = $reflection->getMethod('getLoggerName');
        $method->setAccessible(true);

        $result = $method->invoke($this->generator);

        $this->assertEquals('Article Tag Generator', $result);
    }

    /**
     * Test that payload is created correctly for array content
     */
    public function testPayloadCreationWithCorrectStructure(): void
    {
        // Create test prompt data
        $promptData = [
            'task_type' => 'article_tag_generation',
            'system_prompt' => 'Test system prompt',
            'model' => 'claude-3-sonnet',
            'max_tokens' => 500,
            'temperature' => 0.5,
        ];
        $this->aipromptsTable->save($this->aipromptsTable->newEntity($promptData));

        // Mock the API service to capture the payload
        $capturedPayload = null;
        $mockResponse = $this->createMock(Response::class);

        $this->mockApiService->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function ($payload) use ($mockResponse, &$capturedPayload) {
                $capturedPayload = $payload;

                return $mockResponse;
            });

        $this->mockApiService->expects($this->once())
            ->method('parseResponse')
            ->willReturn(['tags' => 'test']);

        $allTags = ['tag1', 'tag2'];
        $title = 'Test Title';
        $body = 'Test content';

        $this->generator->generateArticleTags($allTags, $title, $body);

        // Verify the payload structure
        $this->assertIsArray($capturedPayload);
        $this->assertArrayHasKey('model', $capturedPayload);
        $this->assertArrayHasKey('max_tokens', $capturedPayload);
        $this->assertArrayHasKey('temperature', $capturedPayload);
        $this->assertArrayHasKey('system', $capturedPayload);
        $this->assertArrayHasKey('messages', $capturedPayload);

        $this->assertEquals('claude-3-sonnet', $capturedPayload['model']);
        $this->assertEquals(500, $capturedPayload['max_tokens']);
        $this->assertEquals(0.5, $capturedPayload['temperature']);
        $this->assertEquals('Test system prompt', $capturedPayload['system']);

        $this->assertIsArray($capturedPayload['messages']);
        $this->assertCount(1, $capturedPayload['messages']);
        $this->assertEquals('user', $capturedPayload['messages'][0]['role']);

        // Content should be JSON encoded
        $expectedContent = json_encode([
            'existing_tags' => $allTags,
            'article_title' => $title,
            'article_content' => $body,
        ]);
        $this->assertEquals($expectedContent, $capturedPayload['messages'][0]['content']);
    }
}
