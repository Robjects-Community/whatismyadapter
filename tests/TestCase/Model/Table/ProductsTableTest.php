<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsTable Test Case
 */
class ProductsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProductsTable
     */
    protected $Products;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Products',
        'app.Users',
        'app.Articles',
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Products') ? [] : ['className' => ProductsTable::class];
        $this->Products = $this->getTableLocator()->get('Products', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Products);

        parent::tearDown();
    }

    public function testGetPublishedProducts(): void
    {
        $result = $this->Products->getPublishedProducts();
        $this->assertInstanceOf('Cake\ORM\Query', $result);

        $products = $result->toArray();
        foreach ($products as $product) {
            $this->assertTrue($product->is_published);
        }
    }

    public function testSearchProducts(): void
    {
        $result = $this->Products->searchProducts('test');
        $this->assertInstanceOf('Cake\ORM\Query', $result);
    }

    public function testValidation(): void
    {
        $product = $this->Products->newEntity([]);
        $errors = $product->getErrors();

        $this->assertArrayHasKey('title', $errors);
    }
}
