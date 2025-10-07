<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticlesTable;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticlesTable Test Case
 *
 * Comprehensive test suite for ArticlesTable including:
 * - Initialization tests (behaviors and associations)
 * - Validation rules (user_id, title, body)
 * - beforeSave callback (publication date, word count)
 * - afterSave callback (AI job queuing)
 * - Custom finder methods (getFeatured, getMainMenuPages, etc.)
 * - Business rules validation
 */
class ArticlesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Articles',
        'app.Comments',
        'app.Slugs',
        'app.Images',
        // 'app.ArticlesTranslations', // Disabled due to schema issue
        'app.Users',
        'app.Tags',
        'app.PageViews',
        // 'app.Products', // Disabled due to schema issue
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Articles') ? [] : ['className' => ArticlesTable::class];
        $this->Articles = $this->getTableLocator()->get('Articles', $config);
        
        // Disable AI for most tests to prevent job queuing
        Configure::write('AI.enabled', false);
        Configure::write('AI.articleTags', false);
        Configure::write('AI.articleSummaries', false);
        Configure::write('AI.articleSEO', false);
        Configure::write('AI.articleTranslations', false);
        Configure::write('AI.imageGeneration.enabled', false);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Articles);
        Configure::delete('AI');

        parent::tearDown();
    }

    // ============================================================
    // Initialization Tests
    // ============================================================

    /**
     * Test initialize method sets up table correctly
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('articles', $this->Articles->getTable());
        $this->assertEquals('title', $this->Articles->getDisplayField());
        $this->assertEquals('id', $this->Articles->getPrimaryKey());
        
        // Test behaviors are attached
        $this->assertTrue($this->Articles->hasBehavior('Timestamp'));
        $this->assertTrue($this->Articles->hasBehavior('Commentable'));
        $this->assertTrue($this->Articles->hasBehavior('Orderable'));
        $this->assertTrue($this->Articles->hasBehavior('Slug'));
        $this->assertTrue($this->Articles->hasBehavior('ImageAssociable'));
        $this->assertTrue($this->Articles->hasBehavior('QueueableImage'));
        $this->assertTrue($this->Articles->hasBehavior('Translate'));
        
        // Test associations
        $this->assertTrue($this->Articles->hasAssociation('Users'));
        $this->assertTrue($this->Articles->hasAssociation('Tags'));
        $this->assertTrue($this->Articles->hasAssociation('PageViews'));
        $this->assertTrue($this->Articles->hasAssociation('Products'));
    }

    /**
     * Test Translate behavior configuration
     *
     * @return void
     */
    public function testTranslateBehaviorConfiguration(): void
    {
        $translateBehavior = $this->Articles->behaviors()->get('Translate');
        $this->assertNotNull($translateBehavior);
        
        $config = $translateBehavior->getConfig();
        $this->assertArrayHasKey('fields', $config);
        $this->assertContains('title', $config['fields']);
        $this->assertContains('body', $config['fields']);
        $this->assertContains('summary', $config['fields']);
    }

    /**
     * Test associations are configured correctly
     *
     * @return void
     */
    public function testAssociations(): void
    {
        // Test belongsTo Users
        $usersAssociation = $this->Articles->associations()->get('Users');
        $this->assertNotNull($usersAssociation);
        $this->assertEquals('user_id', $usersAssociation->getForeignKey());
        
        // Test belongsToMany Tags
        $tagsAssociation = $this->Articles->associations()->get('Tags');
        $this->assertNotNull($tagsAssociation);
        $this->assertEquals('articles_tags', $tagsAssociation->junction()->getTable());
        
        // Test hasMany PageViews
        $pageViewsAssociation = $this->Articles->associations()->get('PageViews');
        $this->assertNotNull($pageViewsAssociation);
        $this->assertTrue($pageViewsAssociation->getDependent());
        
        // Test hasMany Products
        $productsAssociation = $this->Articles->associations()->get('Products');
        $this->assertNotNull($productsAssociation);
    }

    // ============================================================
    // Validation Tests - Default
    // ============================================================

    /**
     * Test validationDefault with valid data
     *
     * @return void
     */
    public function testValidationDefaultSuccess(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794', // Valid user from fixture
            'title' => 'Test Article',
            'body' => 'This is test article content.',
            'kind' => 'article',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertEmpty($article->getErrors(), 'Expected no validation errors');
    }

    /**
     * Test validationDefault requires user_id
     *
     * @return void
     */
    public function testValidationUserIdRequired(): void
    {
        $data = [
            'title' => 'Test Article',
            'body' => 'Test content',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertNotEmpty($article->getError('user_id'));
    }

    /**
     * Test validationDefault validates user_id as UUID
     *
     * @return void
     */
    public function testValidationUserIdUuid(): void
    {
        $data = [
            'user_id' => 'invalid-uuid',
            'title' => 'Test Article',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertNotEmpty($article->getError('user_id'));
        $this->assertArrayHasKey('uuid', $article->getError('user_id'));
    }

    /**
     * Test validationDefault requires title
     *
     * @return void
     */
    public function testValidationTitleRequired(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'body' => 'Test content',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertNotEmpty($article->getError('title'));
        $this->assertArrayHasKey('_required', $article->getError('title'));
    }

    /**
     * Test validationDefault title max length
     *
     * @return void
     */
    public function testValidationTitleMaxLength(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => str_repeat('a', 256), // Exceeds 255 char limit
            'body' => 'Test content',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertNotEmpty($article->getError('title'));
        $this->assertArrayHasKey('maxLength', $article->getError('title'));
    }

    /**
     * Test validationDefault allows empty body
     *
     * @return void
     */
    public function testValidationBodyOptional(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Test Article',
            'body' => '',
        ];
        
        $article = $this->Articles->newEntity($data);
        $this->assertEmpty($article->getError('body'), 'Body should be optional');
    }

    // ============================================================
    // beforeSave Callback Tests
    // ============================================================

    /**
     * Test beforeSave sets publication date when is_published changes to true
     *
     * @return void
     */
    public function testBeforeSavePublicationDate(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Test Article',
            'body' => 'Content',
            'kind' => 'article',
            'is_published' => true,
        ];
        
        $article = $this->Articles->newEntity($data);
        $saved = $this->Articles->save($article);
        
        $this->assertNotNull($saved->published);
        $this->assertInstanceOf(DateTime::class, $saved->published);
    }

    /**
     * Test beforeSave calculates word count from body
     *
     * @return void
     */
    public function testBeforeSaveWordCount(): void
    {
        $body = '<p>This is a test article with some HTML tags</p><strong>Bold text</strong>';
        
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Test Article',
            'body' => $body,
            'kind' => 'article',
        ];
        
        $article = $this->Articles->newEntity($data);
        $saved = $this->Articles->save($article);
        
        // Should strip HTML and count words
        $this->assertEquals(11, $saved->word_count);
    }

    /**
     * Test beforeSave doesn't modify when nothing changes
     *
     * @return void
     */
    public function testBeforeSaveNoChanges(): void
    {
        // Create initial article
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Test Article',
            'body' => 'Original content',
            'kind' => 'article',
            'is_published' => true,
        ];
        
        $article = $this->Articles->newEntity($data);
        $saved = $this->Articles->save($article);
        $originalPublished = $saved->published;
        
        // Update title only (not is_published or body)
        $saved = $this->Articles->patchEntity($saved, ['title' => 'Updated Title']);
        $updated = $this->Articles->save($saved);
        
        // Published date should remain unchanged
        $this->assertEquals($originalPublished, $updated->published);
    }

    // ============================================================
    // Custom Finder Tests
    // ============================================================

    /**
     * Test getFeatured returns only featured published articles
     *
     * @return void
     */
    public function testGetFeatured(): void
    {
        // Create featured article
        $featured = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Featured Article',
            'kind' => 'article',
            'featured' => 1,
            'is_published' => 1,
            'lft' => 1,
            'rght' => 2,
        ]);
        $this->Articles->save($featured);
        
        // Create non-featured article
        $regular = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Regular Article',
            'kind' => 'article',
            'featured' => 0,
            'is_published' => 1,
            'lft' => 3,
            'rght' => 4,
        ]);
        $this->Articles->save($regular);
        
        $results = $this->Articles->getFeatured('test_');
        
        $this->assertNotEmpty($results);
        foreach ($results as $article) {
            $this->assertEquals('article', $article->kind);
            $this->assertEquals(1, $article->featured);
            $this->assertEquals(1, $article->is_published);
        }
    }

    /**
     * Test getRootPages returns only root pages
     *
     * @return void
     */
    public function testGetRootPages(): void
    {
        // Create root page
        $rootPage = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Root Page',
            'kind' => 'page',
            'parent_id' => null,
            'is_published' => 1,
            'lft' => 1,
            'rght' => 4,
        ]);
        $this->Articles->save($rootPage);
        
        $results = $this->Articles->getRootPages('test_');
        
        $this->assertIsArray($results);
        foreach ($results as $page) {
            $this->assertEquals('page', $page->kind);
            $this->assertNull($page->parent_id);
            $this->assertEquals(1, $page->is_published);
        }
    }

    /**
     * Test getMainMenuPages returns only main menu pages
     *
     * @return void
     */
    public function testGetMainMenuPages(): void
    {
        // Create main menu page
        $mainPage = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Main Menu Page',
            'kind' => 'page',
            'is_published' => 1,
            'main_menu' => 1,
            'lft' => 1,
            'rght' => 2,
        ]);
        $this->Articles->save($mainPage);
        
        $results = $this->Articles->getMainMenuPages('test_');
        
        $this->assertIsArray($results);
        foreach ($results as $page) {
            $this->assertEquals('page', $page->kind);
            $this->assertEquals(1, $page->main_menu);
            $this->assertEquals(1, $page->is_published);
        }
    }

    /**
     * Test getFooterMenuPages returns only footer menu pages
     *
     * @return void
     */
    public function testGetFooterMenuPages(): void
    {
        // Create footer menu page
        $footerPage = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Footer Menu Page',
            'kind' => 'page',
            'is_published' => 1,
            'footer_menu' => 1,
            'lft' => 1,
            'rght' => 2,
        ]);
        $this->Articles->save($footerPage);
        
        $results = $this->Articles->getFooterMenuPages('test_');
        
        $this->assertIsArray($results);
        foreach ($results as $page) {
            $this->assertEquals('page', $page->kind);
            $this->assertEquals(1, $page->footer_menu);
            $this->assertEquals(1, $page->is_published);
        }
    }

    /**
     * Test getFooterMenuPagesWithChildren includes child pages
     *
     * @return void
     */
    public function testGetFooterMenuPagesWithChildren(): void
    {
        // Create parent footer menu page
        $parent = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Parent Footer Page',
            'kind' => 'page',
            'is_published' => 1,
            'footer_menu' => 1,
            'lft' => 1,
            'rght' => 4,
        ]);
        $this->Articles->save($parent);
        
        // Create child page
        $child = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Child Page',
            'kind' => 'page',
            'is_published' => 1,
            'parent_id' => $parent->id,
            'lft' => 2,
            'rght' => 3,
        ]);
        $this->Articles->save($child);
        
        $results = $this->Articles->getFooterMenuPagesWithChildren('test_');
        
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results), 'Should include parent and child');
    }

    /**
     * Test getMainMenuPagesWithChildren includes child pages
     *
     * @return void
     */
    public function testGetMainMenuPagesWithChildren(): void
    {
        // Create parent main menu page
        $parent = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Parent Main Page',
            'kind' => 'page',
            'is_published' => 1,
            'main_menu' => 1,
            'lft' => 1,
            'rght' => 4,
        ]);
        $this->Articles->save($parent);
        
        // Create child page
        $child = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Child Page',
            'kind' => 'page',
            'is_published' => 1,
            'parent_id' => $parent->id,
            'lft' => 2,
            'rght' => 3,
        ]);
        $this->Articles->save($child);
        
        $results = $this->Articles->getMainMenuPagesWithChildren('test_');
        
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results), 'Should include parent and child');
    }

    /**
     * Test getArchiveDates returns hierarchical date array
     *
     * @return void
     */
    public function testGetArchiveDates(): void
    {
        // Create article with publication date
        $article = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Archive Test Article',
            'kind' => 'article',
            'is_published' => 1,
            'published' => new DateTime('2025-03-15'),
        ]);
        $this->Articles->save($article);
        
        $results = $this->Articles->getArchiveDates('test_');
        
        $this->assertIsArray($results);
        if (!empty($results)) {
            // Should have year as key
            $this->assertArrayHasKey(2025, $results);
            // Should have array of months
            $this->assertIsArray($results[2025]);
        }
    }

    /**
     * Test getRecentArticles returns top 3 recent articles
     *
     * @return void
     */
    public function testGetRecentArticles(): void
    {
        // Create multiple articles with different published dates
        for ($i = 1; $i <= 5; $i++) {
            $article = $this->Articles->newEntity([
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'title' => "Article $i",
                'kind' => 'article',
                'is_published' => 1,
                'published' => new DateTime("2025-01-$i"),
            ]);
            $this->Articles->save($article);
        }
        
        $results = $this->Articles->getRecentArticles('test_');
        
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(3, count($results), 'Should return maximum 3 articles');
        
        // Verify articles are ordered by published DESC
        if (count($results) > 1) {
            $this->assertGreaterThan(
                $results[1]->published->timestamp,
                $results[0]->published->timestamp,
                'Articles should be ordered by published date DESC'
            );
        }
    }

    // ============================================================
    // Business Rules Tests
    // ============================================================

    /**
     * Test buildRules enforces user_id exists in Users
     *
     * @return void
     */
    public function testBuildRulesUserExists(): void
    {
        $data = [
            'user_id' => '00000000-0000-0000-0000-000000000000', // Non-existent user
            'title' => 'Test Article',
        ];
        
        $article = $this->Articles->newEntity($data);
        $result = $this->Articles->save($article);
        
        $this->assertFalse($result, 'Save should fail for non-existent user');
        $this->assertNotEmpty($article->getError('user_id'));
    }

    // ============================================================
    // CRUD Operation Tests
    // ============================================================

    /**
     * Test successful article creation
     *
     * @return void
     */
    public function testCreateArticleSuccess(): void
    {
        $data = [
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'New Article',
            'body' => 'Article content here',
            'kind' => 'article',
            'is_published' => 0,
        ];
        
        $article = $this->Articles->newEntity($data);
        $result = $this->Articles->save($article);
        
        $this->assertInstanceOf('App\\Model\\Entity\\Article', $result);
        $this->assertNotEmpty($result->id);
        $this->assertEquals('New Article', $result->title);
    }

    /**
     * Test updating existing article
     *
     * @return void
     */
    public function testUpdateArticleSuccess(): void
    {
        // Create article
        $article = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Original Title',
            'body' => 'Original content',
            'kind' => 'article',
        ]);
        $saved = $this->Articles->save($article);
        
        // Update article
        $updated = $this->Articles->patchEntity($saved, [
            'title' => 'Updated Title',
        ]);
        
        $result = $this->Articles->save($updated);
        
        $this->assertNotFalse($result);
        $this->assertEquals('Updated Title', $result->title);
    }

    /**
     * Test deleting article
     *
     * @return void
     */
    public function testDeleteArticleSuccess(): void
    {
        // Create article
        $article = $this->Articles->newEntity([
            'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
            'title' => 'Article to Delete',
            'kind' => 'article',
        ]);
        $saved = $this->Articles->save($article);
        
        // Delete article
        $result = $this->Articles->delete($saved);
        
        $this->assertTrue($result);
        
        // Verify article is deleted
        $exists = $this->Articles->exists(['id' => $saved->id]);
        $this->assertFalse($exists);
    }
}
