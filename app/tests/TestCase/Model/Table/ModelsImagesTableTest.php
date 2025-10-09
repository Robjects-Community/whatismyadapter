<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ModelsImagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ModelsImagesTable Test Case
 */
class ModelsImagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ModelsImagesTable
     */
    protected $ModelsImages;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ModelsImages',
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
        $config = $this->getTableLocator()->exists('ModelsImages') ? [] : ['className' => ModelsImagesTable::class];
        $this->ModelsImages = $this->getTableLocator()->get('ModelsImages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ModelsImages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ModelsImagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ModelsImagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
