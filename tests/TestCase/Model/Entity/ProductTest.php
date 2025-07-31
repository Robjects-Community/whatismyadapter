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
    protected $Products;

    protected array $fixtures = ['app.Products', 'app.Users', 'app.Tags', 'app.ProductsTags'
    ];

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

    //imported from legacy code functions for Articles - replaced article keywords with product
            public function testSlugGenerationAndUniqueness(): void
    {

        // TODO: NEED TO REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES and ASSERT THAT THE SLUG IS GENERATED CORRECTLY WHEN A NEW PRODUCT IS CREATED
        // This creates a new product entity with a user ID defined prior in fixtures to perform slug generation
        // Test slug generation -  This creates a new product entity with a user ID defined prior in fixtures to perform slug generation
        $productWithSlug = $this->Products->newEntity([
            'title' => 'New Test Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new test product.',
            'slug' => '',
            'kind' => 'adapter',
        ]);
        $this->Products->save($productWithSlug); // Save the product to trigger slug generation

        //// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        // Assert that the slug is generated correctly
        // // Assert that the slug is generated correctly
        //$this->assertEquals('new-test-product', $productWithSlug->slug, 'Slug should match the expected format for the new product');



        // Test slug uniqueness - this creates a new product entity with a user ID defined prior in fixtures on the same slug - for example, Product One has a slug of 'product-one' so 
        $duplicateProduct = $this->Products->newEntity([
            'title' => 'Product One', // This title already exists in fixtures
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is another test product with the same title as an existing one.',
            'slug' => 'new-test-product',
            'kind' => 'adapter',
        ]);
        $result = $this->Products->save($duplicateProduct);
        // Assert that the save operation fails due to duplicate slug
        ////// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        //$this->assertFalse($result, 'Save operation should fail due to duplicate slug');
        $expectedErrors = [
            'slug' => [
                'unique' => 'This slug is already in use.',
            ],
        ];

        ///// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        // Assert that the errors match the expected structure
        //$this->assertEquals($expectedErrors, $duplicateProduct->getErrors(), 'Error message for duplicate slug should match expected format');

        // Test slug generation with special characters
        $specialCharProduct = $this->Products->newEntity([
            'title' => 'Test: Product with Special Characters!&',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with special characters in the title.',
            'slug' => '',
            'kind' => 'adapter',
        ]);
        // Save the product to trigger slug generation
        $this->Products->save($specialCharProduct);

        //// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        // Assert that the slug is generated correctly
        //$this->assertNotEmpty($specialCharProduct->slug, 'Slug for product with special characters should not be empty');
        //$this->assertEquals('test-product-with-special-characters', $specialCharProduct->slug, 'Slug should be properly formatted without special characters');

        // Test slug generation with very long title
        $longTitleProduct = $this->Products->newEntity([
            'title' => str_repeat('Very Long Title ', 20), // 300 characters
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with a very long title.',
            'slug' => '',
            'kind' => 'adapter',
        ]);
        $result = $this->Products->save($longTitleProduct);

        //// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        // Assert that the save operation failed
        //$this->assertFalse($result, 'Save operation should fail due to title length');

        // Define the expected error structure
        $expectedErrors = [
            'title' => [
                'maxLength' => 'The provided value must be at most `255` characters long',
            ],
        ];

        //// TODO: REFACTOR THIS TEST TO USE THE NEW PRODUCTS TABLE AND FIXTURES
        // Assert that the errors match the expected structure
        //$this->assertEquals($expectedErrors, $longTitleProduct->getErrors(), 'Error message for title max length should match expected format');
    }
    /**
     * Test price formatting
     *
     * @return void
     */
    public function testGetPrice(): void
    {

               // Test slug generation
        $productWithPrice = $this->Products->newEntity([
            'title' => '30 dollar Test Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new test product.',
            'kind' => 'adapter',
            'price' => 29.99,
            'currency' => 'USD',
        ]);
        $this->Products->save($productWithPrice); // Save the product to trigger slug generation


        // // Assuming you have a virtual field or method for formatted price
        // $expected = '29.99';

        $this->assertEquals('29.99', $productWithPrice->price);
    }

    /**
     * Test product display name
     *
     * @return void
     */
    public function testGetTitle(): void
    {
         // Test slug generation
        $product = $this->Products->newEntity([
            'title' => 'New Test Product',
            'user_id' => 'product-6509480c-e7e6--1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new test product.',
            'slug' => '',
            'kind' => 'adapter',

        ]);
        $this->Products->save($product); // Save the product to trigger slug generation

        $this->assertEquals('New Test Product', $product->title, 'Title should match the expected format');


// $this->assertEquals('New Test Product', $product->title,
//         //test the display name accessor
//         $product = $this->Products->get('prod-001-usb-c-cable');
//         $expected = 'Test Product'; // Assuming the title is 'Test Product'
//         $this->assertEquals($expected, $product->display_name);
        // // Test a virtual field that combines manufacturer and title
        // $expected = 'Test Product';
        // $this->assertEquals($expected, $this->Product->title);
    }

    /**
     * Test verification status accessor
     *
     * @return void
     */
    public function testIsVerified(): void
    {
        // test the verification status accessor
        $verified_product = $this->Products->newEntity([
            'title' => 'Test Product',
            'user_id' => 'product-6509480c-e7e6--1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new test product.',
            'slug' => '',
            'kind' => 'adapter',
            'verification_status' => 'approved',
        ]);

        $verified_product->verification_status = 'approved';
        $this->assertTrue($verified_product->is_verified); // Should be true for 'approved' status

        $verified_product->verification_status = 'pending';
        $this->assertFalse($verified_product->is_verified); // Should be false for 'pending' status
    }


}
