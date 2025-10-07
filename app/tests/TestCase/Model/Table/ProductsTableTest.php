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
        'app.Slugs',
        'app.Users',
        'app.Articles',
        'app.Tags',
        'app.ProductsReliability',
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

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getPublishedProducts method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getPublishedProducts()
     */
    public function testGetPublishedProducts(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getProductsByStatus method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getProductsByStatus()
     */
    public function testGetProductsByStatus(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test searchProducts method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::searchProducts()
     */
    public function testSearchProducts(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getRelatedProducts method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getRelatedProducts()
     */
    public function testGetRelatedProducts(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test incrementViewCount method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::incrementViewCount()
     */
    public function testIncrementViewCount(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test advancedSearch method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::advancedSearch()
     */
    public function testAdvancedSearch(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findForQuiz method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::findForQuiz()
     */
    public function testFindForQuiz(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test scoreWithAi method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::scoreWithAi()
     */
    public function testScoreWithAi(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getByPortCompatibility method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getByPortCompatibility()
     */
    public function testGetByPortCompatibility(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getByDeviceCompatibility method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getByDeviceCompatibility()
     */
    public function testGetByDeviceCompatibility(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getCertifiedProducts method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getCertifiedProducts()
     */
    public function testGetCertifiedProducts(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getProductsNeedingNormalization method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getProductsNeedingNormalization()
     */
    public function testGetProductsNeedingNormalization(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test insertSampleData method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::insertSampleData()
     */
    public function testInsertSampleData(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addImageValidationRules method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::addImageValidationRules()
     */
    public function testAddImageValidationRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addRequiredImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::addRequiredImageValidation()
     */
    public function testAddRequiredImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test addOptionalImageValidation method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::addOptionalImageValidation()
     */
    public function testAddOptionalImageValidation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::log()
     */
    public function testLog(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test translation method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::translation()
     */
    public function testTranslation(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productNeedsImage method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productNeedsImage()
     */
    public function testProductNeedsImage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productHasImages method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productHasImages()
     */
    public function testProductHasImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test isImageGenerationEnabled method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::isImageGenerationEnabled()
     */
    public function testIsImageGenerationEnabled(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productTypeShouldHaveImages method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productTypeShouldHaveImages()
     */
    public function testProductTypeShouldHaveImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productHasSufficientContent method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productHasSufficientContent()
     */
    public function testProductHasSufficientContent(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productExcludedFromGeneration method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productExcludedFromGeneration()
     */
    public function testProductExcludedFromGeneration(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findProductsNeedingImages method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::findProductsNeedingImages()
     */
    public function testFindProductsNeedingImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test countProductsNeedingImages method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::countProductsNeedingImages()
     */
    public function testCountProductsNeedingImages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test productIdNeedsImage method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::productIdNeedsImage()
     */
    public function testProductIdNeedsImage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getProductsNeedingImagesBatch method
     *
     * @return void
     * @link \App\Model\Table\ProductsTable::getProductsNeedingImagesBatch()
     */
    public function testGetProductsNeedingImagesBatch(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
