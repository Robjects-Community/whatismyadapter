<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsReliabilityFieldsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsReliabilityFieldsTable Test Case
 */
class ProductsReliabilityFieldsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsReliabilityFieldsTable
     */
    protected $ProductsReliabilityFields;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ProductsReliabilityFields',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProductsReliabilityFields') ? [] : ['className' => ProductsReliabilityFieldsTable::class];
        $this->ProductsReliabilityFields = $this->getTableLocator()->get('ProductsReliabilityFields', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProductsReliabilityFields);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findFieldsFor method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::findFieldsFor()
     */
    public function testFindFieldsFor(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findFieldsForMultiple method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::findFieldsForMultiple()
     */
    public function testFindFieldsForMultiple(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFieldStats method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::getFieldStats()
     */
    public function testGetFieldStats(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findLowScoringField method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::findLowScoringField()
     */
    public function testFindLowScoringField(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findMissingField method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::findMissingField()
     */
    public function testFindMissingField(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFieldWeights method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::getFieldWeights()
     */
    public function testGetFieldWeights(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getTopPerformingFields method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityFieldsTable::getTopPerformingFields()
     */
    public function testGetTopPerformingFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
