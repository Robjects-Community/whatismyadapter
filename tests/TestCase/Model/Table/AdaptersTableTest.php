<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AdaptersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AdaptersTable Test Case
 */
class AdaptersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AdaptersTable
     */
    protected $Adapters;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Adapters',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Adapters') ? [] : ['className' => AdaptersTable::class];
        $this->Adapters = $this->getTableLocator()->get('Adapters', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Adapters);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\AdaptersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
