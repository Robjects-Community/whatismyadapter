<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\Google;

use App\Service\Api\Google\GoogleApiService;
use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * GoogleApiService Test Case
 *
 * Tests for Google Translate API service functionality
 */
class GoogleApiServiceTest extends TestCase
{
    protected GoogleApiService $googleApiService;

    public function setUp(): void
    {
        parent::setUp();
        // Skip actual API calls for tests
        $this->googleApiService = $this->getMockBuilder(GoogleApiService::class)
            ->onlyMethods(['__construct'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that gallery placeholders are properly preserved during preprocessing
     */
    public function testPreprocessContentPreservesGalleryPlaceholders(): void
    {
        $content = 'Here is some text with a gallery: [gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title] and more text.';

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that the gallery placeholder was replaced with a preservation placeholder
        $this->assertStringContainsString('<!--PRESERVED_BLOCK_', $result);
        $this->assertStringNotContainsString('[gallery:', $result);
        $this->assertStringContainsString('Here is some text with a gallery:', $result);
        $this->assertStringContainsString('and more text.', $result);

        // Check that the original placeholder was stored
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertNotEmpty($preservedBlocks);
        $this->assertStringContainsString('[gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title]', implode('', $preservedBlocks));
    }

    /**
     * Test that postprocessing restores gallery placeholders
     */
    public function testPostprocessContentRestoresGalleryPlaceholders(): void
    {
        $originalContent = '[gallery:123e4567-e89b-12d3-a456-426614174000:grid:My Gallery Title]';

        // Use reflection to access private methods and properties
        $reflection = new ReflectionClass(GoogleApiService::class);

        $preprocessMethod = $reflection->getMethod('preprocessContent');
        $preprocessMethod->setAccessible(true);

        $postprocessMethod = $reflection->getMethod('postprocessContent');
        $postprocessMethod->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        // Preprocess to create placeholders
        $preprocessedContent = $preprocessMethod->invoke($this->googleApiService, $originalContent);

        // Postprocess to restore original content
        $restoredContent = $postprocessMethod->invoke($this->googleApiService, $preprocessedContent);

        // Original content should be fully restored
        $this->assertEquals($originalContent, $restoredContent);
    }

    /**
     * Test that code blocks and YouTube placeholders are still preserved
     */
    public function testPreprocessContentPreservesExistingPatterns(): void
    {
        $codeBlock = "```php\necho \"hello\";\n```";
        $content = "Text with code {$codeBlock} and YouTube [youtube:dQw4w9WgXcQ:1:2:test] and gallery [gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test].";

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Check that placeholders were created (at least for gallery and youtube)
        $this->assertStringNotContainsString('[youtube:', $result);
        $this->assertStringNotContainsString('[gallery:', $result);
        $this->assertGreaterThanOrEqual(2, substr_count($result, '<!--PRESERVED_BLOCK_'));

        // Check that preserved content includes our patterns
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $allPreservedContent = implode('', $preservedBlocks);
        $this->assertStringContainsString('[youtube:dQw4w9WgXcQ:1:2:test]', $allPreservedContent);
        $this->assertStringContainsString('[gallery:123e4567-e89b-12d3-a456-426614174000:grid:Test]', $allPreservedContent);
    }

    /**
     * Test edge cases with malformed gallery placeholders
     */
    public function testPreprocessContentWithMalformedPlaceholders(): void
    {
        // These should NOT be matched and preserved
        $content = 'Text with [gallery:invalid] and [gallery:123:] and [gallery] and normal text.';

        // Use reflection to access private method
        $reflection = new ReflectionClass(GoogleApiService::class);
        $method = $reflection->getMethod('preprocessContent');
        $method->setAccessible(true);

        $preservedBlocksProperty = $reflection->getProperty('preservedBlocks');
        $preservedBlocksProperty->setAccessible(true);
        $preservedBlocksProperty->setValue($this->googleApiService, []);

        $result = $method->invoke($this->googleApiService, $content);

        // Malformed placeholders should remain unchanged
        $this->assertStringContainsString('[gallery:invalid]', $result);
        $this->assertStringContainsString('[gallery:123:]', $result);
        $this->assertStringContainsString('[gallery]', $result);
        $this->assertStringNotContainsString('<!--PRESERVED_BLOCK_', $result);

        // No blocks should be preserved
        $preservedBlocks = $preservedBlocksProperty->getValue($this->googleApiService);
        $this->assertEmpty($preservedBlocks);
    }
}
