<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BlockedIpsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BlockedIpsTable Test Case
 */
class BlockedIpsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BlockedIpsTable
     */
    protected $BlockedIps;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.BlockedIps',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('BlockedIps') ? [] : ['className' => BlockedIpsTable::class];
        $this->BlockedIps = $this->getTableLocator()->get('BlockedIps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->BlockedIps);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\BlockedIpsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\BlockedIpsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
