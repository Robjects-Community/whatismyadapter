<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InternationalisationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InternationalisationsTable Test Case
 */
class InternationalisationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InternationalisationsTable
     */
    protected $Internationalisations;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Internationalisations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Internationalisations') ? [] : ['className' => InternationalisationsTable::class];
        $this->Internationalisations = $this->getTableLocator()->get('Internationalisations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Internationalisations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\InternationalisationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
