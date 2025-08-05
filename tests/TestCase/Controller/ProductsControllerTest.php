<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AppControllerTest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\I18n\DateTime;

/**
 * App\Controller\ProductsController Test Case
 *
 * @uses \App\Controller\ProductsController
 */
class ProductsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Products',
        'app.Users', 
        'app.Articles',
        'app.Tags',
        'app.ProductsTags',
    ];

    /**
     * Test add method GET request
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddGet(): void
    {
        // Test unauthenticated access
        $this->get('/products/add');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);

        // Test authenticated access
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'd26de9a6-7122-4a99-8185-01ca706ff536',
                    'email' => 'admin@example.com',
                    'is_admin' => true
                ]
            ]
        ]);

        $this->get('/products/add');
        $this->assertResponseOk();
        $this->assertTemplate('add');
        
        // Check that form fields are present
        $this->assertResponseContains('name="title"');
        $this->assertResponseContains('name="description"');
        $this->assertResponseContains('name="manufacturer"');
        $this->assertResponseContains('name="model_number"');
        $this->assertResponseContains('name="price"');
    }

    /**
     * Test add method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostSuccess(): void
    {
        // Authenticate user
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'd26de9a6-7122-4a99-8185-01ca706ff536',
                    'email' => 'admin@example.com',
                    'is_admin' => true
                ]
            ]
        ]);

        $data = [
            'title' => 'USB-C to HDMI Adapter',
            'slug' => 'usb-c-to-hdmi-adapter',
            'description' => 'High-quality USB-C to HDMI adapter supporting 4K resolution',
            'manufacturer' => 'Anker',
            'model_number' => 'A8306',
            'price' => 29.99,
            'currency' => 'USD',
            'alt_text' => 'USB-C to HDMI adapter product image',
            'is_published' => 1,
            'featured' => 0,
            'verification_status' => 'pending',
            // Adapter-specific fields from your CSV data
            'connector_type_a' => 'USB-C',
            'connector_type_b' => 'HDMI',
            'supports_usb_pd' => 1,
            'max_power_delivery' => '60W',
            'usb_version' => 'USB 3.1',
            'supports_displayport' => 1,
            'supports_hdmi' => 1,
            'supports_alt_mode' => 1,
            'supports_thunderbolt' => 0,
            'supports_quick_charge' => 0,
            'supports_audio' => 1,
            'cable_length' => '0.2m',
            'wire_gauge' => '24 AWG',
            'shielding_type' => 'Foil+Braid',
            'is_active_cable' => 0,
            'category_rating' => 'Premium'
        ];

        $this->post('/products/add', $data);
        
        // Should redirect to index on success
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The product has been saved.');

        // Verify the product was actually saved
        $products = $this->getTableLocator()->get('Products');
        $product = $products->find()
            ->where(['title' => 'USB-C to HDMI Adapter'])
            ->first();
        
        $this->assertNotNull($product);
        $this->assertEquals('Anker', $product->manufacturer);
        $this->assertEquals(29.99, $product->price);
        $this->assertEquals('USB-C', $product->connector_type_a);
        $this->assertEquals('HDMI', $product->connector_type_b);
        $this->assertEquals(1, $product->supports_usb_pd);
        $this->assertEquals('Premium', $product->category_rating);
    }

    /**
     * Test add method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostValidationErrors(): void
    {
        // Authenticate user
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'd26de9a6-7122-4a99-8185-01ca706ff536',
                    'email' => 'admin@example.com',
                    'is_admin' => true
                ]
            ]
        ]);

        // Test with missing required fields
        $data = [
            'title' => '', // Empty title should cause validation error
            'description' => 'Test description',
            'price' => 'invalid_price', // Invalid price format
        ];

        $this->post('/products/add', $data);
        
        // Should stay on add page with errors
        $this->assertResponseOk();
        $this->assertTemplate('add');
        $this->assertFlashMessage('The product could not be saved. Please, try again.');
        
        // Check for validation error messages
        $this->assertResponseContains('This field cannot be left empty');
    }

    /**
     * Test add method with duplicate slug
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostDuplicateSlug(): void
    {
        // Authenticate user
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'd26de9a6-7122-4a99-8185-01ca706ff536',
                    'email' => 'admin@example.com',
                    'is_admin' => true
                ]
            ]
        ]);

        // First, create a product
        $data1 = [
            'title' => 'Test Product 1',
            'slug' => 'test-product',
            'description' => 'First test product',
            'manufacturer' => 'Test Manufacturer',
            'price' => 19.99,
        ];

        $this->post('/products/add', $data1);
        $this->assertRedirect(['action' => 'index']);

        // Now try to create another with the same slug
        $data2 = [
            'title' => 'Test Product 2',
            'slug' => 'test-product', // Same slug
            'description' => 'Second test product',
            'manufacturer' => 'Test Manufacturer',
            'price' => 29.99,
        ];

        $this->post('/products/add', $data2);
        
        // Should stay on add page with slug validation error
        $this->assertResponseOk();
        $this->assertTemplate('add');
        $this->assertResponseContains('This value is already in use');
    }

    /**
     * Test add method with CSV-style adapter data
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostWithCsvAdapterData(): void
    {
        // Authenticate user
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'd26de9a6-7122-4a99-8185-01ca706ff536',
                    'email' => 'admin@example.com',
                    'is_admin' => true
                ]
            ]
        ]);

        // Test data based on your Current-tasks.csv structure
        $csvData = [
            'title' => 'USB-C to USB-A Adapter',
            'description' => 'Premium USB-C to USB-A adapter with power delivery',
            'manufacturer' => 'Generic',
            'price' => 19.99,
            'currency' => 'USD',
            'connector_type_a' => 'USB-C',
            'connector_type_b' => 'USB-A',
            'supports_usb_pd' => 1,
            'max_power_delivery' => '20V/5A',
            'usb_version' => 'USB 3.1',
            'supports_displayport' => 1,
            'supports_hdmi' => 0,
            'supports_alt_mode' => 1,
            'supports_thunderbolt' => 0,
            'supports_quick_charge' => 1,
            'supports_audio' => 1,
            'cable_length' => '0.15m',
            'wire_gauge' => '28 AWG',
            'shielding_type' => 'Basic',
            'is_active_cable' => 0,
            'category_rating' => 'Standard',
            'verification_status' => 'pending'
        ];

        $this->post('/products/add', $csvData);
        $this->assertRedirect(['action' => 'index']);

        // Verify all adapter-specific fields were saved correctly
        $products = $this->getTableLocator()->get('Products');
        $product = $products->find()
            ->where(['title' => 'USB-C to USB-A Adapter'])
            ->first();

        $this->assertNotNull($product);
        $this->assertEquals('USB-C', $product->connector_type_a);
        $this->assertEquals('USB-A', $product->connector_type_b);  
        $this->assertEquals('20V/5A', $product->max_power_delivery);
        $this->assertEquals('USB 3.1', $product->usb_version);
        $this->assertEquals('0.15m', $product->cable_length);
        $this->assertEquals('28 AWG', $product->wire_gauge);
        $this->assertEquals('Basic', $product->shielding_type);
        $this->assertEquals('Standard', $product->category_rating);
        $this->assertEquals(1, $product->supports_usb_pd);
        $this->assertEquals(0, $product->supports_hdmi);
        $this->assertEquals(1, $product->supports_displayport);
    }

    /**
     * Test add method authorization for non-admin users
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostUnauthorized(): void
    {
        // Test with regular user (non-admin)
        $this->session([
            'Auth' => [
                'User' => [
                    'id' => 'regular-user-id',
                    'email' => 'user@example.com',
                    'is_admin' => false
                ]
            ]
        ]);

        $data = [
            'title' => 'Test Product',
            'description' => 'Test description',
            'price' => 19.99,
        ];

        $this->post('/products/add', $data);
        
        // Should be redirected or show authorization error
        // Adjust based on your authorization logic
        $this->assertRedirect();
    }

    /**
     * Test remaining methods (keeping original structure for compatibility)
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
