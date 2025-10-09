<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UserAccountConfirmationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UserAccountConfirmationsTable Test Case
 */
class UserAccountConfirmationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UserAccountConfirmationsTable
     */
    protected $UserAccountConfirmations;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.UserAccountConfirmations',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('UserAccountConfirmations') ? [] : ['className' => UserAccountConfirmationsTable::class];
        $this->UserAccountConfirmations = $this->getTableLocator()->get('UserAccountConfirmations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UserAccountConfirmations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UserAccountConfirmationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UserAccountConfirmationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
