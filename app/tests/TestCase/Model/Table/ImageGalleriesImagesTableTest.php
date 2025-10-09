<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImageGalleriesImagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImageGalleriesImagesTable Test Case
 */
class ImageGalleriesImagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImageGalleriesImagesTable
     */
    protected $ImageGalleriesImages;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleriesImages',
        'app.ImageGalleries',
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
        $config = $this->getTableLocator()->exists('ImageGalleriesImages') ? [] : ['className' => ImageGalleriesImagesTable::class];
        $this->ImageGalleriesImages = $this->getTableLocator()->get('ImageGalleriesImages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageGalleriesImages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::afterSave()
     */
    public function testAfterSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterDelete method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::afterDelete()
     */
    public function testAfterDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test reorderImages method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::reorderImages()
     */
    public function testReorderImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getNextPosition method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::getNextPosition()
     */
    public function testGetNextPosition(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findOrdered method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::findOrdered()
     */
    public function testFindOrdered(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\ImageGalleriesImagesTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
