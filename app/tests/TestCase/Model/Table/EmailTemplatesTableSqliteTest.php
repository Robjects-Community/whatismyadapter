<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class EmailTemplatesTableSqliteTest extends TestCase
{
    protected array $fixtures = ['app.EmailTemplates'];

    public function testFixtureLoadsAndCounts(): void
    {
        $table = TableRegistry::getTableLocator()->get('EmailTemplates');
        $this->assertSame(1, $table->find()->count());
    }

    public function testValidationRequiresNameAndSubject(): void
    {
        $table = TableRegistry::getTableLocator()->get('EmailTemplates');
        $entity = $table->newEntity([
            // omit name and subject
        ]);
        $this->assertNotEmpty($entity->getErrors());
    }
}
