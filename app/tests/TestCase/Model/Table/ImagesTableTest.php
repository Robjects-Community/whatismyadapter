<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImagesTable Test Case
 */
class ImagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImagesTable
     */
    protected $Images;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Images',
        'app.ImageGalleries',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Images') ? [] : ['className' => ImagesTable::class];
        $this->Images = $this->getTableLocator()->get('Images', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Images);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationCreate method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::validationCreate()
     */
    public function testValidationCreate(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationUpdate method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::validationUpdate()
     */
    public function testValidationUpdate(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addImageValidationRules method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::addImageValidationRules()
     */
    public function testAddImageValidationRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addRequiredImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::addRequiredImageValidation()
     */
    public function testAddRequiredImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addOptionalImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::addOptionalImageValidation()
     */
    public function testAddOptionalImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJob method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::queueJob()
     */
    public function testQueueJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJobs method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::queueJobs()
     */
    public function testQueueJobs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueDelayedJob method
     *
     * @return void
     * @link \App\Model\Table\ImagesTable::queueDelayedJob()
     */
    public function testQueueDelayedJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
