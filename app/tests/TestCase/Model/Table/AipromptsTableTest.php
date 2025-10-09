<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AipromptsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AipromptsTable Test Case
 */
class AipromptsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AipromptsTable
     */
    protected $Aiprompts;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Aiprompts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Aiprompts') ? [] : ['className' => AipromptsTable::class];
        $this->Aiprompts = $this->getTableLocator()->get('Aiprompts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Aiprompts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AipromptsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findIndexedById method
     *
     * @return void
     * @link \App\Model\Table\AipromptsTable::findIndexedById()
     */
    public function testFindIndexedById(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
