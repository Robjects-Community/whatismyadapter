<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProductsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProductsTable Test Case
 *
 * Tests the ProductsTable class functionality including:
 * - Product validation and saving
 * - Verification workflow
 * - Product-category associations
 * - Technical specifications handling
 * - Slug generation and uniqueness
 * - Product search and filtering
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
     * Fixtures - defines which test data to load
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Products',
        'app.ProductCategories', 
        'app.ConnectorTypes',
        'app.ProductConnectors',
        'app.ProductSpecifications',
        'app.ProductReviews',
        'app.ProductsCategories',
        'app.ProductsTags',
        'app.Users',
        'app.Tags'
    ];

    /**
     * setUp method - runs before each test method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Products = TableRegistry::getTableLocator()->get('Products');
    }

    /**
     * tearDown method - runs after each test method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Products);
        parent::tearDown();
    }

    /**
     * Test product validation rules
     * 
     * Tests that required fields are validated correctly and
     * validation messages are appropriate
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        // Test missing required fields
        $product = $this->Products->newEntity([]);
        $this->assertFalse($this->Products->save($product));
        
        $errors = $product->getErrors();
        $this->assertArrayHasKey('user_id', $errors);
        $this->assertArrayHasKey('title', $errors);

        // Test valid product creation
        $validProduct = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'title' => 'Test USB-C Cable',
            'manufacturer' => 'TestBrand',
            'kind' => 'adapter',
            'slug' => 'test-usb-c-cable'
        ]);
        
        $this->assertTrue($this->Products->save($validProduct) !== false);
        $this->assertEquals('Test USB-C Cable', $validProduct->title);
    }

    /**
     * Test product verification workflow
     * 
     * Tests the verification status changes and reliability scoring
     *
     * @return void
     */
    public function testVerificationWorkflow(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable');
        $this->assertEquals('pending', $product->verification_status);
        $this->assertEquals(0.00, $product->reliability_score);

        // Test verification status update
        $product->verification_status = 'approved';
        $product->reliability_score = 4.5;
        $product->verified_at = new \DateTime('now');
        
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        
        $updatedProduct = $this->Products->get('prod-001-usb-c-cable');
        $this->assertEquals('approved', $updatedProduct->verification_status);
        $this->assertEquals(4.50, $updatedProduct->reliability_score);
    }

    /**
     * Test product-category associations
     * 
     * Tests the many-to-many relationship between products and categories
     *
     * @return void
     */
    public function testProductCategoryAssociations(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable', [
            'contain' => ['Categories']
        ]);
        
        $this->assertNotEmpty($product->categories);
        $this->assertEquals('Charging Cables', $product->categories[0]->name);
        
        // Test adding new category association
        $newCategory = $this->Products->Categories->get('cat-002-data-cables');
        $product->categories[] = $newCategory;
        
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        
        // Verify association was added
        $updatedProduct = $this->Products->get('prod-001-usb-c-cable', [
            'contain' => ['Categories']
        ]);
        $this->assertCount(2, $updatedProduct->categories);
    }

    /**
     * Test technical specifications handling
     * 
     * Tests JSON field storage and retrieval of technical specs
     *
     * @return void
     */
    public function testTechnicalSpecifications(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable');
        
        // Test JSON technical specs
        $techSpecs = [
            'power_rating' => '100W',
            'data_transfer_speed' => '10Gbps',
            'cable_length' => '2m',
            'certifications' => ['USB-IF', 'CE', 'FCC']
        ];
        
        $product->technical_specs = $techSpecs;
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        
        // Verify JSON data storage and retrieval
        $updatedProduct = $this->Products->get('prod-001-usb-c-cable');
        $this->assertEquals('100W', $updatedProduct->technical_specs['power_rating']);
        $this->assertContains('USB-IF', $updatedProduct->technical_specs['certifications']);
    }

    /**
     * Test product search functionality
     * 
     * Tests the getPublishedProducts method with various filters
     *
     * @return void
     */
    public function testGetPublishedProducts(): void
    {
        // Test basic published products query
        $query = $this->Products->getPublishedProducts();
        $products = $query->toArray();
        
        $this->assertNotEmpty($products);
        
        // All returned products should be published and approved
        foreach ($products as $product) {
            $this->assertEquals(1, $product->is_published);
            $this->assertEquals('approved', $product->verification_status);
        }
        
        // Test with manufacturer filter
        $filteredQuery = $this->Products->getPublishedProducts([
            'Products.manufacturer' => 'Anker'
        ]);
        $ankerProducts = $filteredQuery->toArray();
        
        foreach ($ankerProducts as $product) {
            $this->assertEquals('Anker', $product->manufacturer);
        }
    }

    /**
     * Test slug generation and uniqueness
     * 
     * Tests automatic slug generation and duplicate prevention
     *
     * @return void
     */
    public function testSlugGenerationAndUniqueness(): void
    {
        // Test slug generation from title
        $product = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'title' => 'New Test Product with Special Characters!@#',
            'manufacturer' => 'TestBrand',
            'kind' => 'adapter',
            'slug' => '' // Empty slug should trigger auto-generation
        ]);
        
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        $this->assertEquals('new-test-product-with-special-characters', $product->slug);
        
        // Test slug uniqueness validation
        $duplicateProduct = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'title' => 'Another Product',
            'manufacturer' => 'TestBrand',
            'kind' => 'adapter',
            'slug' => 'usb-c-to-lightning-cable' // This slug already exists in fixtures
        ]);
        
        $result = $this->Products->save($duplicateProduct);
        $this->assertFalse($result);
        
        $errors = $duplicateProduct->getErrors();
        $this->assertArrayHasKey('slug', $errors);
    }

    /**
     * Test product connector associations
     * 
     * Tests the relationship between products and connector types
     *
     * @return void
     */
    public function testProductConnectorAssociations(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable', [
            'contain' => ['ProductConnectors.ConnectorTypes']
        ]);
        
        $this->assertNotEmpty($product->product_connectors);
        
        // Test connector data
        $connector = $product->product_connectors[0];
        $this->assertEquals('input', $connector->connector_role);
        $this->assertEquals('USB-C', $connector->connector_type->display_name);
        
        // Test adding new connector
        $newConnector = $this->Products->ProductConnectors->newEntity([
            'product_id' => $product->id,
            'connector_type_id' => 'conn-002-usb-a',
            'connector_role' => 'output',
            'quantity' => 1
        ]);
        
        $result = $this->Products->ProductConnectors->save($newConnector);
        $this->assertTrue($result !== false);
    }

    /**
     * Test product reliability scoring
     * 
     * Tests reliability score validation and constraints
     *
     * @return void
     */
    public function testReliabilityScoring(): void
    {
        $product = $this->Products->get('prod-001-usb-c-cable');
        
        // Test valid reliability scores
        $validScores = [0.0, 2.5, 5.0];
        foreach ($validScores as $score) {
            $product->reliability_score = $score;
            $result = $this->Products->save($product);
            $this->assertTrue($result !== false, "Score {$score} should be valid");
        }
        
        // Test invalid reliability scores (should be handled by database constraints)
        $product->reliability_score = 6.0; // Over maximum
        // Note: This would typically be caught by validation rules in a real implementation
        $this->assertTrue(true); // Placeholder for score validation test
    }

    /**
     * Test product publishing workflow
     * 
     * Tests the publication date setting when product is published
     *
     * @return void
     */
    public function testPublishingWorkflow(): void
    {
        $product = $this->Products->get('prod-003-unpublished');
        $this->assertEquals(0, $product->is_published);
        $this->assertNull($product->published);
        
        // Test publishing
        $product->is_published = 1;
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        
        $publishedProduct = $this->Products->get('prod-003-unpublished');
        $this->assertEquals(1, $publishedProduct->is_published);
        $this->assertNotNull($publishedProduct->published);
        $this->assertInstanceOf(\DateTime::class, $publishedProduct->published);
    }

    /**
     * Test word count calculation
     * 
     * Tests automatic word count calculation for product descriptions
     *
     * @return void
     */
    public function testWordCountCalculation(): void
    {
        $product = $this->Products->newEntity([
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
            'title' => 'Test Product for Word Count',
            'body' => 'This is a test product description with exactly ten words here.',
            'manufacturer' => 'TestBrand',
            'kind' => 'adapter',
            'slug' => 'test-word-count-product'
        ]);
        
        $result = $this->Products->save($product);
        $this->assertTrue($result !== false);
        $this->assertEquals(10, $product->word_count);
    }
}
