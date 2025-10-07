<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticlesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticlesTable Test Case
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
        'app.ArticlesTranslations',
        'app.Users',
        'app.Tags',
        'app.PageViews',
        'app.Products',
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
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test beforeSave method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::beforeSave()
     */
    public function testBeforeSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::afterSave()
     */
    public function testAfterSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFeatured method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getFeatured()
     */
    public function testGetFeatured(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getRootPages method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getRootPages()
     */
    public function testGetRootPages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getMainMenuPages method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getMainMenuPages()
     */
    public function testGetMainMenuPages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFooterMenuPages method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getFooterMenuPages()
     */
    public function testGetFooterMenuPages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFooterMenuPagesWithChildren method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getFooterMenuPagesWithChildren()
     */
    public function testGetFooterMenuPagesWithChildren(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getMainMenuPagesWithChildren method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getMainMenuPagesWithChildren()
     */
    public function testGetMainMenuPagesWithChildren(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test shouldInheritFromParent method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::shouldInheritFromParent()
     */
    public function testShouldInheritFromParent(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getArchiveDates method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getArchiveDates()
     */
    public function testGetArchiveDates(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getRecentArticles method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getRecentArticles()
     */
    public function testGetRecentArticles(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test articleNeedsImage method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::articleNeedsImage()
     */
    public function testArticleNeedsImage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hasExistingImage method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::hasExistingImage()
     */
    public function testHasExistingImage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueImageGenerationJob method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::queueImageGenerationJob()
     */
    public function testQueueImageGenerationJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test batchQueueImageGeneration method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::batchQueueImageGeneration()
     */
    public function testBatchQueueImageGeneration(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findArticlesNeedingImages method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::findArticlesNeedingImages()
     */
    public function testFindArticlesNeedingImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getImageGenerationStats method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::getImageGenerationStats()
     */
    public function testGetImageGenerationStats(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addImageValidationRules method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::addImageValidationRules()
     */
    public function testAddImageValidationRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addRequiredImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::addRequiredImageValidation()
     */
    public function testAddRequiredImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addOptionalImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::addOptionalImageValidation()
     */
    public function testAddOptionalImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJob method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::queueJob()
     */
    public function testQueueJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJobs method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::queueJobs()
     */
    public function testQueueJobs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueDelayedJob method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::queueDelayedJob()
     */
    public function testQueueDelayedJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::emptySeoFields()
     */
    public function testEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptyTranslationFields method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::emptyTranslationFields()
     */
    public function testEmptyTranslationFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test updateEmptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::updateEmptySeoFields()
     */
    public function testUpdateEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test translation method
     *
     * @return void
     * @link \App\Model\Table\ArticlesTable::translation()
     */
    public function testTranslation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
