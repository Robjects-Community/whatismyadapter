<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Article;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Article Entity Test Case
 *
 * This test demonstrates comprehensive entity testing patterns for CakePHP 5.x,
 * including testing traits, virtual fields, validation, and business logic.
 * Unlike ArticleTest.php which tests Table functionality, this focuses on Entity behavior.
 */
class ArticleEntityTest extends TestCase
{
    /**
     * Test Article entity creation with basic data
     *
     * @return void
     */
    public function testEntityCreationWithBasicData(): void
    {
        $data = [
            'title' => 'Test Article Title',
            'lede' => 'This is a test article lede',
            'body' => 'This is the main body content of the test article.',
            'user_id' => 'test-user-123',
            'published' => true,
            'is_published' => true,
            'kind' => 'article',
        ];

        $article = new Article($data);

        $this->assertEquals('Test Article Title', $article->title);
        $this->assertEquals('This is a test article lede', $article->lede);
        $this->assertEquals('This is the main body content of the test article.', $article->body);
        $this->assertEquals('test-user-123', $article->user_id);
        $this->assertTrue($article->published);
        $this->assertTrue($article->is_published);
        $this->assertEquals('article', $article->kind);
    }

    /**
     * Test mass assignment security with protected fields
     *
     * @return void
     */
    public function testMassAssignmentSecurity(): void
    {
        $data = [
            'id' => 'should-not-be-set',
            'title' => 'Test Article',
            'created' => new DateTime('2024-01-01 12:00:00'),
            'modified' => new DateTime('2024-01-01 12:00:00'),
        ];

        $article = new Article($data);

        // When using the constructor directly, CakePHP sets all fields
        // The $_accessible array is only enforced when using Table::newEntity() or patchEntity()
        // So we test that the field is marked as not accessible
        $this->assertFalse($article->isAccessible('id'), 'ID field should be marked as not accessible');

        // But allowed fields should be set
        $this->assertEquals('Test Article', $article->title);
        $this->assertInstanceOf(DateTime::class, $article->created);
        $this->assertInstanceOf(DateTime::class, $article->modified);
    }

    /**
     * Test SEO entity trait functionality
     *
     * @return void
     */
    public function testSeoEntityTrait(): void
    {
        $article = new Article([
            'title' => 'Main Article Title',
            'lede' => 'Article lede content',
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom meta description',
            'meta_keywords' => 'test, article, seo',
            'facebook_description' => 'Facebook description',
            'twitter_description' => 'Twitter description',
        ]);

        // Test hasSeoContent
        $this->assertTrue($article->hasSeoContent());

        // Test getSeoData
        $seoData = $article->getSeoData();
        $this->assertIsArray($seoData);
        $this->assertEquals('Custom Meta Title', $seoData['meta_title']);
        $this->assertEquals('Custom meta description', $seoData['meta_description']);
        $this->assertEquals('test, article, seo', $seoData['meta_keywords']);
        $this->assertEquals('Facebook description', $seoData['facebook_description']);
        $this->assertEquals('Twitter description', $seoData['twitter_description']);
        $this->assertNull($seoData['linkedin_description']);
        $this->assertNull($seoData['instagram_description']);

        // Test effective meta title (should use custom meta_title)
        $this->assertEquals('Custom Meta Title', $article->getEffectiveMetaTitle());

        // Test effective meta description (should use custom meta_description)
        $this->assertEquals('Custom meta description', $article->getEffectiveMetaDescription());
    }

    /**
     * Test SEO trait fallback functionality
     *
     * @return void
     */
    public function testSeoTraitFallbacks(): void
    {
        $article = new Article([
            'title' => 'Main Title',
            'lede' => 'Main lede content',
            // No meta_title or meta_description set
        ]);

        // Check that the entity has the expected title and lede
        $this->assertEquals('Main Title', $article->title);
        $this->assertEquals('Main lede content', $article->lede);

        // Should fallback to title for meta title when meta_title is empty
        $this->assertNull($article->meta_title, 'meta_title should be null initially');
        $effectiveTitle = $article->getEffectiveMetaTitle();
        $this->assertEquals('Main Title', $effectiveTitle, 'Should fallback to title when meta_title is empty');

        // Should fallback to lede for meta description
        $effectiveDesc = $article->getEffectiveMetaDescription();
        $this->assertEquals('Main lede content', $effectiveDesc);

        // Should not have SEO content when only fallbacks exist
        $this->assertFalse($article->hasSeoContent());
    }

    /**
     * Test setSeoData bulk update functionality
     *
     * @return void
     */
    public function testSetSeoDataBulkUpdate(): void
    {
        $article = new Article(['title' => 'Test Article']);

        $seoData = [
            'meta_title' => 'New Meta Title',
            'meta_description' => 'New meta description',
            'facebook_description' => 'New Facebook description',
            'invalid_field' => 'Should be ignored',
        ];

        $result = $article->setSeoData($seoData);

        // Should return the entity for method chaining
        $this->assertSame($article, $result);

        // Should set valid SEO fields
        $this->assertEquals('New Meta Title', $article->meta_title);
        $this->assertEquals('New meta description', $article->meta_description);
        $this->assertEquals('New Facebook description', $article->facebook_description);

        // Should ignore invalid fields (they shouldn't be set as properties)
        $this->assertObjectNotHasProperty('invalid_field', $article);
    }

    /**
     * Test article with hierarchical data (nested articles)
     *
     * @return void
     */
    public function testHierarchicalArticleData(): void
    {
        $parentArticle = new Article([
            'title' => 'Parent Article',
            'kind' => 'page',
            'lft' => 1,
            'rght' => 4,
        ]);

        $childArticle = new Article([
            'title' => 'Child Article',
            'parent_id' => 'parent-123',
            'kind' => 'page',
            'lft' => 2,
            'rght' => 3,
        ]);

        $this->assertEquals('Parent Article', $parentArticle->title);
        $this->assertEquals('page', $parentArticle->kind);
        $this->assertEquals(1, $parentArticle->lft);
        $this->assertEquals(4, $parentArticle->rght);

        $this->assertEquals('Child Article', $childArticle->title);
        $this->assertEquals('parent-123', $childArticle->parent_id);
        $this->assertEquals('page', $childArticle->kind);
        $this->assertEquals(2, $childArticle->lft);
        $this->assertEquals(3, $childArticle->rght);
    }

    /**
     * Test article publishing states
     *
     * @return void
     */
    public function testPublishingStates(): void
    {
        // Published article
        $publishedArticle = new Article([
            'title' => 'Published Article',
            'published' => true,
            'is_published' => true,
        ]);

        $this->assertTrue($publishedArticle->published);
        $this->assertTrue($publishedArticle->is_published);

        // Draft article
        $draftArticle = new Article([
            'title' => 'Draft Article',
            'published' => false,
            'is_published' => false,
        ]);

        $this->assertFalse($draftArticle->published);
        $this->assertFalse($draftArticle->is_published);
    }

    /**
     * Test article feature flags
     *
     * @return void
     */
    public function testFeatureFlags(): void
    {
        $article = new Article([
            'title' => 'Featured Article',
            'featured' => true,
            'main_menu' => true,
            'footer_menu' => false,
        ]);

        $this->assertTrue($article->featured);
        $this->assertTrue($article->main_menu);
        $this->assertFalse($article->footer_menu);
    }

    /**
     * Test article word count functionality
     *
     * @return void
     */
    public function testWordCount(): void
    {
        $article = new Article([
            'title' => 'Test Article',
            'body' => 'This is a test article with exactly ten words in the body.',
            'word_count' => 10,
        ]);

        $this->assertEquals(10, $article->word_count);
        $this->assertIsInt($article->word_count);
    }

    /**
     * Test article with markdown content
     *
     * @return void
     */
    public function testMarkdownContent(): void
    {
        $markdownContent = "# Test Article\n\nThis is **bold** and this is *italic*.";

        $article = new Article([
            'title' => 'Markdown Article',
            'markdown' => $markdownContent,
            'body' => '<h1>Test Article</h1><p>This is <strong>bold</strong> and this is <em>italic</em>.</p>',
        ]);

        $this->assertEquals($markdownContent, $article->markdown);
        $this->assertStringContainsString('<h1>Test Article</h1>', $article->body);
        $this->assertStringContainsString('<strong>bold</strong>', $article->body);
    }

    /**
     * Test article summary functionality
     *
     * @return void
     */
    public function testSummary(): void
    {
        $article = new Article([
            'title' => 'Test Article',
            'body' => 'This is a long article body with lots of content.',
            'summary' => 'This is a short summary of the article.',
        ]);

        $this->assertEquals('This is a short summary of the article.', $article->summary);
    }

    /**
     * Test article with all SEO social media descriptions
     *
     * @return void
     */
    public function testAllSocialMediaDescriptions(): void
    {
        $article = new Article([
            'title' => 'Social Media Article',
            'facebook_description' => 'Facebook-optimized description',
            'linkedin_description' => 'LinkedIn-optimized description',
            'twitter_description' => 'Twitter-optimized description',
            'instagram_description' => 'Instagram-optimized description',
        ]);

        $seoData = $article->getSeoData();
        $this->assertEquals('Facebook-optimized description', $seoData['facebook_description']);
        $this->assertEquals('LinkedIn-optimized description', $seoData['linkedin_description']);
        $this->assertEquals('Twitter-optimized description', $seoData['twitter_description']);
        $this->assertEquals('Instagram-optimized description', $seoData['instagram_description']);

        $this->assertTrue($article->hasSeoContent());
    }

    /**
     * Test article data serialization
     *
     * @return void
     */
    public function testDataSerialization(): void
    {
        $article = new Article([
            'title' => 'Serialization Test',
            'published' => true,
            'created' => new DateTime('2024-01-01 12:00:00'),
        ]);

        // Test toArray functionality
        $arrayData = $article->toArray();
        $this->assertIsArray($arrayData);
        $this->assertEquals('Serialization Test', $arrayData['title']);
        $this->assertTrue($arrayData['published']);

        // Test JSON serialization
        $jsonData = json_encode($article);
        $this->assertIsString($jsonData);
        $decodedData = json_decode($jsonData, true);
        $this->assertEquals('Serialization Test', $decodedData['title']);
    }

    /**
     * Test empty article entity
     *
     * @return void
     */
    public function testEmptyArticle(): void
    {
        $article = new Article();

        $this->assertNull($article->title);
        $this->assertNull($article->body);
        $this->assertNull($article->user_id);
        $this->assertFalse($article->hasSeoContent());

        $seoData = $article->getSeoData();
        $this->assertIsArray($seoData);
        $this->assertEmpty(array_filter($seoData)); // All SEO fields should be null/empty
    }

    /**
     * Test article with image data
     *
     * @return void
     */
    public function testArticleWithImage(): void
    {
        $article = new Article([
            'title' => 'Article with Image',
            'image' => 'test-image.jpg',
        ]);

        $this->assertEquals('test-image.jpg', $article->image);
    }

    /**
     * Test article accessibility features
     *
     * @return void
     */
    public function testAccessibilityFields(): void
    {
        $data = [
            'title' => 'Accessible Article',
            'lede' => 'Article lede',
            'featured' => true,
            'main_menu' => false,
            'footer_menu' => true,
            'body' => 'Main content',
            'markdown' => '# Header',
            'summary' => 'Article summary',
            'word_count' => 50,
            'kind' => 'article',
            'published' => true,
            'is_published' => true,
        ];

        $article = new Article($data);

        // Test all accessible fields are properly set
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $article->{$key}, "Field {$key} should be accessible and set correctly");
        }

        // Test that the accessible array includes all expected fields
        $accessible = $article->getAccessible();
        $expectedFields = [
            'user_id', 'title', 'lede', 'featured', 'main_menu', 'footer_menu',
            'slug', 'body', 'markdown', 'summary', 'created', 'modified',
            'word_count', 'kind', 'parent_id', 'lft', 'rght', 'published',
            'is_published', 'tags', 'images', 'image', 'meta_title',
            'meta_description', 'meta_keywords', 'facebook_description',
            'linkedin_description', 'twitter_description', 'instagram_description',
        ];

        foreach ($expectedFields as $field) {
            $this->assertTrue($accessible[$field] ?? false, "Field {$field} should be accessible");
        }
    }

    /**
     * Test translate trait integration
     *
     * @return void
     */
    public function testTranslateTrait(): void
    {
        $article = new Article([
            'title' => 'English Title',
            'body' => 'English body content',
        ]);

        // Test that the entity uses TranslateTrait
        $this->assertContains('Cake\ORM\Behavior\Translate\TranslateTrait', class_uses($article));
    }
}
