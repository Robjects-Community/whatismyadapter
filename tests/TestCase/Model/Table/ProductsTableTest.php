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
     * @var \App\Model\Table\ProductsTable
     */
    protected $Products;


    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
                'app.Slugs',
                'app.Products',
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
        $config = $this->getTableLocator()->exists('Slugs') ? [] : ['className' => ProductsTable::class];
        $this->Products = $this->getTableLocator()->get('Slugs', $config);
    //     $this->Products = $this->getTableLocator()->get('Products');
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
    /**
     * Test reordering products within the tree structure.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testProductTreeReordering(): void
    {
        // Test moving a product to root level
        $data = [
            'id' => 'prod-002-hdmi-adapter', // Child product
            'newParentId' => 'root',
            'newIndex' => 0,
        ];

        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to move product to root level');

        $reorderedProduct = $this->Products->get('prod-002-hdmi-adapter');
        $this->assertNull($reorderedProduct->parent_id, 'Parent ID should be null for root level');
        $this->assertLessThan($reorderedProduct->rght, $reorderedProduct->lft, 'Left value should be less than right value');

        // Test moving a product to a new parent
        $data = [
            'id' => 'prod-003-unpublished', // Test product
            'newParentId' => 'prod-001-usb-c-cable', // New parent
            'newIndex' => 0,
        ];

        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to move product to a new parent');

        $reorderedProduct = $this->Products->get('prod-003-unpublished');
        $this->assertEquals('prod-001-usb-c-cable', $reorderedProduct->parent_id, 'Parent ID should match the new parent');

        $siblings = $this->Products->find('children', for: $reorderedProduct->parent_id, direct: true)->toArray();
        $this->assertEquals(0, array_search($reorderedProduct->id, array_column($siblings, 'id')), 'Reordered product should be the first child');

        // Test reordering within siblings
        $data = [
            'id' => 'prod-002-hdmi-adapter',
            'newParentId' => 'prod-001-usb-c-cable',
            'newIndex' => 1,
        ];

        $result = $this->Products->reorder($data);
        $this->assertTrue($result, 'Failed to reorder within siblings');

        $reorderedProduct = $this->Products->get('prod-002-hdmi-adapter');
        $this->assertEquals('prod-001-usb-c-cable', $reorderedProduct->parent_id, 'Parent ID should remain the same');

        $siblings = $this->Products->find('children', for: $reorderedProduct->parent_id, direct: true)->toArray();
        $this->assertGreaterThan(0, count($siblings), 'There should be siblings after reordering');
        $this->assertEquals(1, array_search($reorderedProduct->id, array_column($siblings, 'id')), 'Reordered product should be the second child');
    }

    /**
     * Test the slug generation and uniqueness in the beforeSave callback.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If saving fails.
     */
    public function testSlugGenerationAndUniqueness(): void
    {
        // Test slug generation
        $product = $this->Products->newEntity([
            'title' => 'New USB-C Hub',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Using an existing user ID from fixtures
            'body' => 'This is a new USB-C hub for testing.',
            'slug' => '',
            'kind' => 'hub',
            'manufacturer' => 'Test Brand',
            'product_code' => 'TB-HUB-001',
        ]);

        $this->Products->save($product);
        $this->assertEquals('new-usb-c-hub', $product->slug, 'Slug should match the expected format');

        // Test slug uniqueness
        $duplicateProduct = $this->Products->newEntity([
            'title' => 'USB-C to Lightning Cable', // This title already exists in fixtures
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is another test product with the same title as an existing one.',
            'slug' => 'new-usb-c-hub',
            'kind' => 'cable',
            'manufacturer' => 'Test Brand',
            'product_code' => 'TB-CABLE-002',
        ]);

        $result = $this->Products->save($duplicateProduct);
        $this->assertFalse($result, 'Save operation should fail due to duplicate slug');

        $expectedErrors = [
            'slug' => [
                'unique' => 'This slug is already in use.',
            ],
        ];
        $this->assertEquals($expectedErrors, $duplicateProduct->getErrors(), 'Error message for duplicate slug should match expected format');

        // Test slug generation with special characters
        $specialCharProduct = $this->Products->newEntity([
            'title' => 'Test: USB-C Hub with Special Characters!&',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with special characters in the title.',
            'slug' => '',
            'kind' => 'hub',
            'manufacturer' => 'Special Brand',
            'product_code' => 'SB-HUB-003',
        ]);

        $this->Products->save($specialCharProduct);
        $this->assertNotEmpty($specialCharProduct->slug, 'Slug for product with special characters should not be empty');
        $this->assertEquals('test-usb-c-hub-with-special-characters', $specialCharProduct->slug, 'Slug should be properly formatted without special characters');

        // Test slug generation with very long title
        $longTitleProduct = $this->Products->newEntity([
            'title' => str_repeat('Very Long Product Title ', 20), // 500 characters
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'This is a test product with a very long title.',
            'slug' => '',
            'kind' => 'adapter',
            'manufacturer' => 'Long Name Brand',
            'product_code' => 'LNB-LONG-004',
        ]);

        $result = $this->Products->save($longTitleProduct);

        // Assert that the save operation failed
        $this->assertFalse($result, 'Save operation should fail due to title length');

        // Define the expected error structure
        $expectedErrors = [
            'title' => [
                'maxLength' => 'The provided value must be at most `255` characters long',
            ],
        ];

        // Assert that the errors match the expected structure
        $this->assertEquals($expectedErrors, $longTitleProduct->getErrors(), 'Error message for title max length should match expected format');
    }

    /**
     * Test product-specific field validation and defaults.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testProductSpecificValidation(): void
    {
        // Test default values are set correctly
        $product = $this->Products->newEntity([
            'title' => 'Test Product Defaults',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'Testing default values for products.',
        ]);

        $this->Products->save($product);
        
        $this->assertEquals('adapter', $product->kind, 'Default kind should be "adapter"');
        $this->assertEquals('USD', $product->currency, 'Default currency should be "USD"');
        $this->assertEquals('in_stock', $product->availability_status, 'Default availability should be "in_stock"');
        $this->assertEquals('developer', $product->entry_input_type, 'Default entry input type should be "developer"');
        $this->assertEquals('pending', $product->verification_status, 'Default verification status should be "pending"');
        $this->assertEquals(0.00, $product->reliability_score, 'Default reliability score should be 0.00');

        // Test price validation (must be positive)
        $invalidPriceProduct = $this->Products->newEntity([
            'title' => 'Invalid Price Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'Testing invalid price validation.',
            'price' => -10.99,
        ]);

        $result = $this->Products->save($invalidPriceProduct);
        $this->assertFalse($result, 'Save operation should fail for negative price');

        // Test reliability score validation (0.00 to 5.00)
        $invalidScoreProduct = $this->Products->newEntity([
            'title' => 'Invalid Score Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'Testing invalid reliability score.',
            'reliability_score' => 6.00,
        ]);

        $result = $this->Products->save($invalidScoreProduct);
        $this->assertFalse($result, 'Save operation should fail for reliability score > 5.00');

        // Test valid product with all fields
        $validProduct = $this->Products->newEntity([
            'title' => 'Complete Test Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'Complete product with all valid fields.',
            'kind' => 'cable',
            'manufacturer' => 'Test Manufacturer',
            'model_number' => 'TM-001',
            'product_code' => 'TM-CABLE-001',
            'price' => 29.99,
            'currency' => 'EUR',
            'availability_status' => 'out_of_stock',
            'stock_quantity' => 0,
            'reliability_score' => 4.25,
            'entry_input_type' => 'user_submission',
            'verification_status' => 'approved',
            'technical_specs' => json_encode(['power' => '60W', 'data_speed' => '10Gbps']),
            'connector_info' => json_encode(['input' => 'USB-C', 'output' => 'USB-A']),
            'compatibility_info' => json_encode(['devices' => ['MacBook', 'iPad', 'iPhone']]),
        ]);

        $result = $this->Products->save($validProduct);
        $this->assertTrue($result, 'Save operation should succeed for valid complete product');
        $this->assertEquals('cable', $validProduct->kind, 'Kind should be set correctly');
        $this->assertEquals('Test Manufacturer', $validProduct->manufacturer, 'Manufacturer should be set correctly');
        $this->assertEquals(29.99, $validProduct->price, 'Price should be set correctly');
        $this->assertEquals('EUR', $validProduct->currency, 'Currency should be set correctly');
    }

    /**
     * Test JSON field handling for technical specifications.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testJsonFieldHandling(): void
    {
        $product = $this->Products->newEntity([
            'title' => 'JSON Fields Test Product',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'body' => 'Testing JSON field handling.',
            'technical_specs' => [
                'power_rating' => '100W',
                'data_transfer' => '40Gbps',
                'video_support' => '4K@60Hz',
                'certifications' => ['USB-IF', 'CE', 'FCC']
            ],
            'connector_info' => [
                'input_connectors' => [
                    ['type' => 'USB-C', 'quantity' => 1]
                ],
                'output_connectors' => [
                    ['type' => 'HDMI', 'quantity' => 1],
                    ['type' => 'USB-A', 'quantity' => 2]
                ]
            ],
            'compatibility_info' => [
                'supported_devices' => ['MacBook Pro', 'MacBook Air', 'iPad Pro'],
                'supported_os' => ['macOS', 'Windows 10+', 'Chrome OS'],
                'minimum_requirements' => ['USB-C port with power delivery']
            ]
        ]);

        $result = $this->Products->save($product);
        $this->assertTrue($result, 'Product with JSON fields should save successfully');

        // Retrieve and verify JSON data
        $savedProduct = $this->Products->get($product->id);
        
        $this->assertIsArray($savedProduct->technical_specs, 'Technical specs should be decoded as array');
        $this->assertEquals('100W', $savedProduct->technical_specs['power_rating'], 'Technical specs should contain correct power rating');
        $this->assertContains('USB-IF', $savedProduct->technical_specs['certifications'], 'Technical specs should contain certifications');

        $this->assertIsArray($savedProduct->connector_info, 'Connector info should be decoded as array');
        $this->assertEquals('USB-C', $savedProduct->connector_info['input_connectors'][0]['type'], 'Connector info should contain correct input type');

        $this->assertIsArray($savedProduct->compatibility_info, 'Compatibility info should be decoded as array');
        $this->assertContains('MacBook Pro', $savedProduct->compatibility_info['supported_devices'], 'Compatibility info should contain supported devices');
    }

    /**
     * Test product verification status transitions.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testVerificationStatusTransitions(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable');
        $this->assertEquals('approved', $product->verification_status, 'Product should start as approved');

        // Test transition to under_review
        $product->verification_status = 'under_review';
        $product->verification_notes = 'Reviewing technical specifications';
        $result = $this->Products->save($product);
        $this->assertTrue($result, 'Should be able to transition to under_review');

        // Test transition to needs_revision
        $product->verification_status = 'needs_revision';
        $product->verification_notes = 'Missing power rating specifications';
        $result = $this->Products->save($product);
        $this->assertTrue($result, 'Should be able to transition to needs_revision');

        // Test transition back to approved with verified timestamp
        $product->verification_status = 'approved';
        $product->verified_at = date('Y-m-d H:i:s');
        $product->verified_by = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $result = $this->Products->save($product);
        $this->assertTrue($result, 'Should be able to approve product');
        $this->assertNotNull($product->verified_at, 'Verified timestamp should be set');
        $this->assertNotNull($product->verified_by, 'Verified by user should be set');
    }

    /**
     * Test finding products by various criteria.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testProductFinders(): void
    {
        // Test finding published products
        $publishedProducts = $this->Products->find('published')->toArray();
        $this->assertGreaterThan(0, count($publishedProducts), 'Should find published products');
        
        foreach ($publishedProducts as $product) {
            $this->assertTrue($product->is_published, 'All found products should be published');
        }

        // Test finding products by manufacturer
        $appleProducts = $this->Products->find()
            ->where(['manufacturer' => 'Apple'])
            ->toArray();
        
        foreach ($appleProducts as $product) {
            $this->assertEquals('Apple', $product->manufacturer, 'All found products should be from Apple');
        }

        // Test finding products by verification status
        $approvedProducts = $this->Products->find()
            ->where(['verification_status' => 'approved'])
            ->toArray();
        
        foreach ($approvedProducts as $product) {
            $this->assertEquals('approved', $product->verification_status, 'All found products should be approved');
        }

        // Test finding products by reliability score range
        $highQualityProducts = $this->Products->find()
            ->where(['reliability_score >=' => 4.0])
            ->toArray();
        
        foreach ($highQualityProducts as $product) {
            $this->assertGreaterThanOrEqual(4.0, $product->reliability_score, 'All found products should have high reliability score');
        }
    }

    /**
     * Test product tree structure queries.
     *
     * @return void
     * @throws \PHPUnit\Framework\AssertionFailedError If any assertion fails.
     */
    public function testProductTreeQueries(): void
    {
        // Test finding children of a product
        $parentProduct = $this->Products->find()
            ->where(['parent_id IS' => null])
            ->first();
        
        if ($parentProduct) {
            $children = $this->Products->find('children', for: $parentProduct->id)->toArray();
            
            foreach ($children as $child) {
                $this->assertEquals($parentProduct->id, $child->parent_id, 'Child should belong to parent');
                $this->assertGreaterThan($parentProduct->lft, $child->lft, 'Child lft should be greater than parent lft');
                $this->assertLessThan($parentProduct->rght, $child->rght, 'Child rght should be less than parent rght');
            }
        }

        // Test finding path to product
        $childProduct = $this->Products->find()
            ->where(['parent_id IS NOT' => null])
            ->first();
        
        if ($childProduct) {
            $path = $this->Products->find('path', for: $childProduct->id)->toArray();
            $this->assertGreaterThan(0, count($path), 'Should find path to child product');
            $this->assertEquals($childProduct->id, end($path)->id, 'Last item in path should be the child product');
        }
    }

}
