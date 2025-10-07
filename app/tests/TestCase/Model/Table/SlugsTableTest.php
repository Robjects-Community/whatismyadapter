<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SlugsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SlugsTable Test Case
 */
class SlugsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SlugsTable
     */
    protected $Slugs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Slugs',
        'app.Articles',
        'app.ImageGalleries',
        'app.Products',
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Slugs') ? [] : ['className' => SlugsTable::class];
        $this->Slugs = $this->getTableLocator()->get('Slugs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Slugs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SlugsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\SlugsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findBySlugAndModel method
     *
     * @return void
     * @link \App\Model\Table\SlugsTable::findBySlugAndModel()
     */
    public function testFindBySlugAndModel(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
