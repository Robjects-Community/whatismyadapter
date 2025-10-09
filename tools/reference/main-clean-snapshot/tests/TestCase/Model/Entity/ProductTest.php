<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use Cake\TestSuite\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var \Cake\ORM\Table
     */
    protected $Products;

    /**
     * @var \Cake\ORM\Table
     */
    protected $Users;

    protected array $fixtures = ['app.Products', 'app.Users'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->Products = $this->getTableLocator()->get('Products');
        $this->Users = $this->getTableLocator()->get('Users');
    }

    protected function tearDown(): void
    {
        unset($this->Products, $this->Users);
        parent::tearDown();
    }

    public function testIsOwnedBy(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';

        $user = $this->Users->get($adminId);
        $product = $this->Products->get($productId);

        $this->assertTrue($product->isOwnedBy($user));

        // Test negative case - non-owner should not pass the check
        $nonOwner = $this->Users->get('6509480c-e7e6-4e65-9c38-1423a8d09d02');
        $this->assertFalse($product->isOwnedBy($nonOwner));
    }
}
