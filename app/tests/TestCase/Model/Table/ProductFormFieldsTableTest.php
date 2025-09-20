<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductFormFieldsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductFormFieldsTable Test Case
 */
class ProductFormFieldsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductFormFieldsTable
     */
    protected $ProductFormFields;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ProductFormFields',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProductFormFields') ? [] : ['className' => ProductFormFieldsTable::class];
        $this->ProductFormFields = $this->getTableLocator()->get('ProductFormFields', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProductFormFields);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProductFormFieldsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProductFormFieldsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
