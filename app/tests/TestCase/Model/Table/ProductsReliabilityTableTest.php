<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsReliabilityTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsReliabilityTable Test Case
 */
class ProductsReliabilityTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsReliabilityTable
     */
    protected $ProductsReliability;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ProductsReliability',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProductsReliability') ? [] : ['className' => ProductsReliabilityTable::class];
        $this->ProductsReliability = $this->getTableLocator()->get('ProductsReliability', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProductsReliability);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findSummaryFor method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::findSummaryFor()
     */
    public function testFindSummaryFor(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findSummariesFor method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::findSummariesFor()
     */
    public function testFindSummariesFor(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findTopScoring method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::findTopScoring()
     */
    public function testFindTopScoring(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findNeedingAttention method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::findNeedingAttention()
     */
    public function testFindNeedingAttention(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getStatsFor method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::getStatsFor()
     */
    public function testGetStatsFor(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getScoringVersions method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::getScoringVersions()
     */
    public function testGetScoringVersions(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findRecentUpdates method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityTable::findRecentUpdates()
     */
    public function testFindRecentUpdates(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
