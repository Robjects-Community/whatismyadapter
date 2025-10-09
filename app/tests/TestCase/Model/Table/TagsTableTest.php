<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TagsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TagsTable Test Case
 */
class TagsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Tags',
        'app.Slugs',
        'app.TagsTranslations',
        'app.Articles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Tags') ? [] : ['className' => TagsTable::class];
        $this->Tags = $this->getTableLocator()->get('Tags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Tags);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::afterSave()
     */
    public function testAfterSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getSimpleThreadedArray method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::getSimpleThreadedArray()
     */
    public function testGetSimpleThreadedArray(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getRootTags method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::getRootTags()
     */
    public function testGetRootTags(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getMainMenuTags method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::getMainMenuTags()
     */
    public function testGetMainMenuTags(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJob method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::queueJob()
     */
    public function testQueueJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJobs method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::queueJobs()
     */
    public function testQueueJobs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueDelayedJob method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::queueDelayedJob()
     */
    public function testQueueDelayedJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::emptySeoFields()
     */
    public function testEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptyTranslationFields method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::emptyTranslationFields()
     */
    public function testEmptyTranslationFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test updateEmptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::updateEmptySeoFields()
     */
    public function testUpdateEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test translation method
     *
     * @return void
     * @link \App\Model\Table\TagsTable::translation()
     */
    public function testTranslation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
