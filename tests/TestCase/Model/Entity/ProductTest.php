<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Product;
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

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Product = new Product([
            'id' => 'test-product-id',
            'title' => 'Test Product',
            'manufacturer' => 'Test Brand',
            'price' => 29.99,
            'currency' => 'USD'
        ]);
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

    /**
     * Test price formatting
     *
     * @return void
     */
    public function testGetFormattedPrice(): void
    {
        // Assuming you have a virtual field or method for formatted price
        $expected = '$29.99';
        $this->assertEquals($expected, $this->Product->formatted_price);
    }

    /**
     * Test product display name
     *
     * @return void
     */
    public function testGetDisplayName(): void
    {
        // Test a virtual field that combines manufacturer and title
        $expected = 'Test Brand - Test Product';
        $this->assertEquals($expected, $this->Product->display_name);
    }

    /**
     * Test verification status accessor
     *
     * @return void
     */
    public function testIsVerified(): void
    {
        $this->Product->verification_status = 'approved';
        $this->assertTrue($this->Product->is_verified);
        
        $this->Product->verification_status = 'pending';
        $this->assertFalse($this->Product->is_verified);
    }
}
