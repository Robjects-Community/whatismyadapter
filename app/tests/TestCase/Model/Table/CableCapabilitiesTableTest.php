<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CableCapabilitiesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CableCapabilitiesTable Test Case
 */
class CableCapabilitiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CableCapabilitiesTable
     */
    protected $CableCapabilities;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.CableCapabilities',
        'app.Products',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CableCapabilities') ? [] : ['className' => CableCapabilitiesTable::class];
        $this->CableCapabilities = $this->getTableLocator()->get('CableCapabilities', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CableCapabilities);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCapabilityCategories method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::getCapabilityCategories()
     */
    public function testGetCapabilityCategories(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCapabilitiesByCategory method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::getCapabilitiesByCategory()
     */
    public function testGetCapabilitiesByCategory(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCertifiedCapabilities method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::getCertifiedCapabilities()
     */
    public function testGetCertifiedCapabilities(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCapabilityStats method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::getCapabilityStats()
     */
    public function testGetCapabilityStats(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test searchByTechnicalSpecs method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::searchByTechnicalSpecs()
     */
    public function testSearchByTechnicalSpecs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test clearCapabilityCache method
     *
     * @return void
     * @link \App\Model\Table\CableCapabilitiesTable::clearCapabilityCache()
     */
    public function testClearCapabilityCache(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
