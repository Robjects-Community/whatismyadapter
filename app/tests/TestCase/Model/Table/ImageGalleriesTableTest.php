<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImageGalleriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImageGalleriesTable Test Case
 */
class ImageGalleriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImageGalleriesTable
     */
    protected $ImageGalleries;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleries',
        'app.Slugs',
        'app.ImageGalleriesTranslations',
        'app.ImageGalleriesImages',
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
        $config = $this->getTableLocator()->exists('ImageGalleries') ? [] : ['className' => ImageGalleriesTable::class];
        $this->ImageGalleries = $this->getTableLocator()->get('ImageGalleries', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageGalleries);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::afterSave()
     */
    public function testAfterSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test beforeDelete method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::beforeDelete()
     */
    public function testBeforeDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queuePreviewGeneration method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::queuePreviewGeneration()
     */
    public function testQueuePreviewGeneration(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getGalleryForPlaceholder method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::getGalleryForPlaceholder()
     */
    public function testGetGalleryForPlaceholder(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJob method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::queueJob()
     */
    public function testQueueJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJobs method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::queueJobs()
     */
    public function testQueueJobs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueDelayedJob method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::queueDelayedJob()
     */
    public function testQueueDelayedJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::emptySeoFields()
     */
    public function testEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test emptyTranslationFields method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::emptyTranslationFields()
     */
    public function testEmptyTranslationFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test updateEmptySeoFields method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::updateEmptySeoFields()
     */
    public function testUpdateEmptySeoFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test translation method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesTable::translation()
     */
    public function testTranslation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
