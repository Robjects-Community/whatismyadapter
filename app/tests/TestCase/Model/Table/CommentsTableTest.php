<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CommentsTable Test Case
 */
class CommentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CommentsTable
     */
    protected $Comments;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Comments',
        'app.Users',
        'app.Articles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Comments') ? [] : ['className' => CommentsTable::class];
        $this->Comments = $this->getTableLocator()->get('Comments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Comments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::afterSave()
     */
    public function testAfterSave(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJob method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::queueJob()
     */
    public function testQueueJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueJobs method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::queueJobs()
     */
    public function testQueueJobs(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test queueDelayedJob method
     *
     * @return void
     * @link \App\Model\Table\CommentsTable::queueDelayedJob()
     */
    public function testQueueDelayedJob(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
