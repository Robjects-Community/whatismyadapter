<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsReliabilityLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsReliabilityLogsTable Test Case
 */
class ProductsReliabilityLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsReliabilityLogsTable
     */
    protected $ProductsReliabilityLogs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ProductsReliabilityLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProductsReliabilityLogs') ? [] : ['className' => ProductsReliabilityLogsTable::class];
        $this->ProductsReliabilityLogs = $this->getTableLocator()->get('ProductsReliabilityLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProductsReliabilityLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findLogsFor method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::findLogsFor()
     */
    public function testFindLogsFor(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findRecentLogs method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::findRecentLogs()
     */
    public function testFindRecentLogs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findLogsBySource method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::findLogsBySource()
     */
    public function testFindLogsBySource(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findLogsByUser method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::findLogsByUser()
     */
    public function testFindLogsByUser(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findSignificantChanges method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::findSignificantChanges()
     */
    public function testFindSignificantChanges(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test verifyChecksums method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::verifyChecksums()
     */
    public function testVerifyChecksums(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test computeLogChecksum method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::computeLogChecksum()
     */
    public function testComputeLogChecksum(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getActivityBySource method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::getActivityBySource()
     */
    public function testGetActivityBySource(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getScoreTrends method
     *
     * @return void
     * @link \App\Model\Table\ProductsReliabilityLogsTable::getScoreTrends()
     */
    public function testGetScoreTrends(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
