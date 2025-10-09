<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QueueConfigurationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QueueConfigurationsTable Test Case
 */
class QueueConfigurationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QueueConfigurationsTable
     */
    protected $QueueConfigurations;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.QueueConfigurations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('QueueConfigurations') ? [] : ['className' => QueueConfigurationsTable::class];
        $this->QueueConfigurations = $this->getTableLocator()->get('QueueConfigurations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->QueueConfigurations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\QueueConfigurationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\QueueConfigurationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
