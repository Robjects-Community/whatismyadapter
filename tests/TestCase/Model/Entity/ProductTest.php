<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Product Test Case
 */
class ProductTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\Product
     */
    protected $Product;

    protected array $fixtures = ['app.Products', 'app.Users', 'app.Articles', 'app.Tags'];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Products = $this->getTableLocator()->get('Products');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Product);

        parent::tearDown();
    }
}
