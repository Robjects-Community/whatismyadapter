<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class CommentsTableSqliteTest extends TestCase
{
    // Only require the Comments fixture for these focused tests
    protected array $fixtures = ['app.Comments'];

    public function testFixtureLoadsAndCounts(): void
    {
        $table = TableRegistry::getTableLocator()->get('Comments');
        $this->assertSame(1, $table->find()->count());
    }

    public function testValidationRequiresContent(): void
    {
        $table = TableRegistry::getTableLocator()->get('Comments');
        $entity = $table->newEntity([
            'foreign_key' => '10000000-0000-0000-0000-000000000000',
            'model' => 'Articles',
            'user_id' => '00000000-0000-0000-0000-000000000001',
            // 'content' omitted to trigger validation error
        ]);
        $this->assertNotEmpty($entity->getErrors());
    }
}
