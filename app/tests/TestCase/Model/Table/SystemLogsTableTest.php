<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SystemLogsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SystemLogsTable Test Case
 */
class SystemLogsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SystemLogsTable
     */
    protected $SystemLogs;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.SystemLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SystemLogs') ? [] : ['className' => SystemLogsTable::class];
        $this->SystemLogs = $this->getTableLocator()->get('SystemLogs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SystemLogs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SystemLogsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
