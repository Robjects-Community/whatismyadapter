<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SlugBehaviorTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Slugs',
    ];

    /**
     * @var \Cake\ORM\Table
     */
    protected $Articles;

    /**
     * @var \Cake\ORM\Table
     */
    protected $Slugs;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->Slugs = TableRegistry::getTableLocator()->get('Slugs');

        $this->Articles->addBehavior('Slug', [
            'sourceField' => 'title',
            'targetField' => 'slug',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Articles);
        unset($this->Slugs);
        parent::tearDown();
    }

    /**
     * Test automatic slug generation from title
     *
     * @return void
     */
    public function testAutomaticSlugGeneration(): void
    {
        $article = $this->Articles->newEntity([
            'title' => 'New Test Article',
            'body' => 'Content for test article',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'is_published' => true,
            'kind' => 'article',
        ]);

        $result = $this->Articles->save($article);
        $this->assertNotFalse($result);
        $this->assertEquals('new-test-article', $article->slug);
    }

    /**
     * Test slug uniqueness validation
     *
     * @return void
     */
    public function testSlugUniqueness(): void
    {
        // Try to create an article with an existing slug
        $article = $this->Articles->newEntity([
            'title' => 'Article One',
            'body' => 'New content',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'is_published' => true,
            'kind' => 'article',
            'slug' => 'article-one-final', // Use a slug we know exists from the fixture
        ]);

        $result = $this->Articles->save($article);
        $this->assertFalse($result);
        $this->assertNotEmpty($article->getError('slug'));
    }

    /**
     * Test slug history creation
     *
     * @return void
     */
    public function testSlugHistoryCreation(): void
    {
        // Get an existing article
        $article = $this->Articles->get('263a5364-a1bc-401c-9e44-49c23d066a0f');

        // Update its title/slug
        $article->patch([
            'title' => 'Article One Updated Again',
            'slug' => 'article-one-updated-again',
        ]);

        $result = $this->Articles->save($article);
        $this->assertNotFalse($result);

        // Check if a new slug history entry was created
        $slugHistory = $this->Slugs->find()
            ->where([
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'model' => 'Articles',
                'slug' => 'article-one-updated-again',
            ])
            ->first();

        $this->assertNotNull($slugHistory);
        $this->assertEquals('article-one-updated-again', $slugHistory->slug);
    }

    /**
     * Test custom slug setting
     *
     * @return void
     */
    public function testCustomSlugSetting(): void
    {
        $article = $this->Articles->newEntity([
            'title' => 'Test Article',
            'slug' => 'custom-slug-value',
            'body' => 'Content for test article',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'is_published' => true,
            'kind' => 'article',
        ]);

        $result = $this->Articles->save($article);
        $this->assertNotFalse($result);
        $this->assertEquals('custom-slug-value', $article->slug);
    }

    /**
     * Test slug validation against historical slugs
     *
     * @return void
     */
    public function testSlugValidationAgainstHistory(): void
    {
        // Try to create an article with a slug that exists in history
        $article = $this->Articles->newEntity([
            'title' => 'Test Article',
            'slug' => 'article-one-updated', // This exists in the slugs history
            'body' => 'Content for test article',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'is_published' => true,
            'kind' => 'article',
        ]);

        $result = $this->Articles->save($article);
        $this->assertFalse($result);
        $this->assertNotEmpty($article->getError('slug'));
    }

    /**
     * Test slug generation with special characters
     *
     * @return void
     */
    public function testSlugGenerationWithSpecialCharacters(): void
    {
        $article = $this->Articles->newEntity([
            'title' => 'Test & Article! With @ Special # Characters',
            'body' => 'Content for test article',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'is_published' => true,
            'kind' => 'article',
        ]);

        $result = $this->Articles->save($article);
        $this->assertNotFalse($result);
        $this->assertEquals('test-article-with-special-characters', $article->slug);
    }

    /**
     * Test updating an article without changing the slug
     *
     * @return void
     */
    public function testUpdateWithoutSlugChange(): void
    {
        $article = $this->Articles->get('263a5364-a1bc-401c-9e44-49c23d066a0f');
        $originalSlug = $article->slug;

        // Update something other than the title
        $article->body = 'Updated content';
        $result = $this->Articles->save($article);
        $this->assertNotFalse($result);

        $this->assertEquals($originalSlug, $article->slug);

        // Verify no new slug history was created for the same slug
        $slugCount = $this->Slugs->find()
            ->where([
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'model' => 'Articles',
                'slug' => $originalSlug,
            ])
            ->count();

        $this->assertEquals(1, $slugCount);
    }
}
