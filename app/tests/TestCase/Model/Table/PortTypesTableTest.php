<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PortTypesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PortTypesTable Test Case
 */
class PortTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PortTypesTable
     */
    protected $PortTypes;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.PortTypes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PortTypes') ? [] : ['className' => PortTypesTable::class];
        $this->PortTypes = $this->getTableLocator()->get('PortTypes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PortTypes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getPortFamilies method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getPortFamilies()
     */
    public function testGetPortFamilies(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getFormFactorsByFamily method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getFormFactorsByFamily()
     */
    public function testGetFormFactorsByFamily(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getPortsByFamilyAndForm method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getPortsByFamilyAndForm()
     */
    public function testGetPortsByFamilyAndForm(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getElectricalSpecs method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getElectricalSpecs()
     */
    public function testGetElectricalSpecs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getDurabilityInfo method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getDurabilityInfo()
     */
    public function testGetDurabilityInfo(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getPortEvolution method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getPortEvolution()
     */
    public function testGetPortEvolution(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getPortStats method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::getPortStats()
     */
    public function testGetPortStats(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test searchBySpecs method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::searchBySpecs()
     */
    public function testSearchBySpecs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test clearPortCache method
     *
     * @return void
     * @link \App\Model\Table\PortTypesTable::clearPortCache()
     */
    public function testClearPortCache(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
