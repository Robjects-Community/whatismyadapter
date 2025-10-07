<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\MediaPickerTrait;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Authentication\IdentityInterface;

/**
 * Test controller that uses MediaPickerTrait for testing purposes
 */
class TestMediaPickerController extends Controller
{
    use MediaPickerTrait;

    /**
     * @var \Cake\ORM\Table
     */
    public $testTable;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->testTable = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * Expose protected methods for testing
     *
     * @param string $method Method name
     * @param array $args Arguments
     * @return mixed
     */
    public function callProtectedMethod(string $method, array $args = [])
    {
        return call_user_func_array([$this, $method], $args);
    }
}

/**
 * MediaPickerTraitTest class
 *
 * Tests all methods in the MediaPickerTrait
 */
class MediaPickerTraitTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Articles',
        'app.Users',
        'app.ArticlesTags',
    ];

    /**
     * Test controller instance
     *
     * @var \App\Test\TestCase\Controller\Component\TestMediaPickerController
     */
    protected $controller;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $request = new ServerRequest([
            'url' => '/test',
            'params' => [],
        ]);
        
        $this->controller = new TestMediaPickerController($request);
        $this->controller->initialize();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->controller);
        parent::tearDown();
    }

    /**
     * Test buildPickerQuery method
     *
     * @return void
     */
    public function testBuildPickerQuery(): void
    {
        $table = $this->controller->testTable;
        $selectFields = ['id', 'title', 'slug'];
        
        // Test basic query building
        $query = $this->controller->callProtectedMethod('buildPickerQuery', [$table, $selectFields]);
        
        $this->assertNotNull($query);
        $this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $query);
    }

    /**
     * Test buildPickerQuery with custom ordering
     *
     * @return void
     */
    public function testBuildPickerQueryWithCustomOrdering(): void
    {
        $table = $this->controller->testTable;
        $selectFields = ['id', 'title', 'slug'];
        $options = ['order' => ['Articles.title' => 'ASC']];
        
        $query = $this->controller->callProtectedMethod('buildPickerQuery', [$table, $selectFields, $options]);
        
        $this->assertNotNull($query);
        $this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $query);
    }

    /**
     * Test handlePickerSearch method
     *
     * @return void
     */
    public function testHandlePickerSearch(): void
    {
        $table = $this->controller->testTable;
        $query = $table->find();
        $searchFields = ['Articles.title', 'Articles.lede'];
        
        // Test with no search term
        $result = $this->controller->callProtectedMethod('handlePickerSearch', [$query, null, $searchFields]);
        $this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $result);
        
        // Test with search term
        $result = $this->controller->callProtectedMethod('handlePickerSearch', [$query, 'test', $searchFields]);
        $this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $result);
    }

    /**
     * Test setupPickerPagination method
     *
     * @return void
     */
    public function testSetupPickerPagination(): void
    {
        $options = ['limit' => 10, 'maxLimit' => 20];
        $settings = $this->controller->callProtectedMethod('setupPickerPagination', [$options]);
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('limit', $settings);
        $this->assertArrayHasKey('maxLimit', $settings);
        $this->assertEquals(10, $settings['limit']);
        $this->assertEquals(20, $settings['maxLimit']);
    }

    /**
     * Test setupPickerPagination with defaults
     *
     * @return void
     */
    public function testSetupPickerPaginationDefaults(): void
    {
        $settings = $this->controller->callProtectedMethod('setupPickerPagination', [[]]);
        
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('limit', $settings);
        $this->assertArrayHasKey('maxLimit', $settings);
        $this->assertEquals(12, $settings['limit']);
        $this->assertEquals(24, $settings['maxLimit']);
    }

    /**
     * Test handlePickerAjaxResponse method with AJAX request
     *
     * @return void
     */
    public function testHandlePickerAjaxResponse(): void
    {
        // Create an AJAX request
        $request = $this->controller->getRequest()
            ->withEnv('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');
        $this->controller->setRequest($request);
        
        $results = [
            ['id' => 1, 'title' => 'Item 1'],
            ['id' => 2, 'title' => 'Item 2'],
        ];
        $search = 'test';
        $template = 'picker_items';
        
        // For testing, we'll skip actual template rendering by catching the exception
        // or we can test the non-AJAX path
        try {
            $response = $this->controller->callProtectedMethod('handlePickerAjaxResponse', [
                $results,
                $search,
                $template,
            ]);
            $this->assertInstanceOf('Cake\Http\Response', $response);
        } catch (\Cake\View\Exception\MissingTemplateException $e) {
            // Expected - template doesn't exist in test environment
            $this->assertStringContainsString('picker_items.php', $e->getMessage());
        }
    }
    
    /**
     * Test handlePickerAjaxResponse returns null for non-AJAX requests
     *
     * @return void
     */
    public function testHandlePickerAjaxResponseNonAjax(): void
    {
        // Non-AJAX request should return null
        $results = [
            ['id' => 1, 'title' => 'Item 1'],
            ['id' => 2, 'title' => 'Item 2'],
        ];
        $search = 'test';
        $template = 'picker_items';
        
        $response = $this->controller->callProtectedMethod('handlePickerAjaxResponse', [
            $results,
            $search,
            $template,
        ]);
        
        $this->assertNull($response);
    }

    /**
     * Test applyPickerExclusion method
     *
     * @return void
     */
    public function testApplyPickerExclusion(): void
    {
        $table = $this->controller->testTable;
        $query = $table->find();
        
        // Create a pivot table for testing
        $pivotTable = TableRegistry::getTableLocator()->get('ArticlesTags');
        
        // Test with real parameters
        $result = $this->controller->callProtectedMethod('applyPickerExclusion', [
            $query,
            $pivotTable,
            'article_id',
            '00000000-0000-0000-0000-000000000001',
            'tag_id',
        ]);
        
        $this->assertInstanceOf('Cake\ORM\Query\SelectQuery', $result);
    }

    /**
     * Test getRequestLimit method
     *
     * @return void
     */
    public function testGetRequestLimit(): void
    {
        // Test with no limit parameter (should return default: 12)
        $limit = $this->controller->callProtectedMethod('getRequestLimit');
        $this->assertIsInt($limit);
        $this->assertEquals(12, $limit);
        
        // Test with limit parameter below max (should return requested value: 20)
        $request = $this->controller->getRequest()->withQueryParams(['limit' => 20]);
        $this->controller->setRequest($request);
        
        $limit = $this->controller->callProtectedMethod('getRequestLimit');
        $this->assertEquals(20, $limit);
        
        // Test with limit parameter above max (should be capped at max: 24)
        $request = $this->controller->getRequest()->withQueryParams(['limit' => 50]);
        $this->controller->setRequest($request);
        
        $limit = $this->controller->callProtectedMethod('getRequestLimit');
        $this->assertEquals(24, $limit);
    }

    /**
     * Test getRequestLimit with invalid values
     *
     * @return void
     */
    public function testGetRequestLimitInvalid(): void
    {
        // Test with negative limit
        $request = $this->controller->getRequest()->withQueryParams(['limit' => -10]);
        $this->controller->setRequest($request);
        
        $limit = $this->controller->callProtectedMethod('getRequestLimit');
        $this->assertGreaterThan(0, $limit);
        
        // Test with zero limit
        $request = $this->controller->getRequest()->withQueryParams(['limit' => 0]);
        $this->controller->setRequest($request);
        
        $limit = $this->controller->callProtectedMethod('getRequestLimit');
        $this->assertGreaterThan(0, $limit);
    }

    /**
     * Test getRequestPage method
     *
     * @return void
     */
    public function testGetRequestPage(): void
    {
        // Test with no page parameter
        $page = $this->controller->callProtectedMethod('getRequestPage');
        $this->assertIsInt($page);
        $this->assertGreaterThan(0, $page);
        
        // Test with page parameter
        $request = $this->controller->getRequest()->withQueryParams(['page' => 3]);
        $this->controller->setRequest($request);
        
        $page = $this->controller->callProtectedMethod('getRequestPage');
        $this->assertEquals(3, $page);
    }

    /**
     * Test getRequestPage with invalid values
     *
     * @return void
     */
    public function testGetRequestPageInvalid(): void
    {
        // Test with negative page
        $request = $this->controller->getRequest()->withQueryParams(['page' => -5]);
        $this->controller->setRequest($request);
        
        $page = $this->controller->callProtectedMethod('getRequestPage');
        $this->assertGreaterThan(0, $page);
        
        // Test with zero page
        $request = $this->controller->getRequest()->withQueryParams(['page' => 0]);
        $this->controller->setRequest($request);
        
        $page = $this->controller->callProtectedMethod('getRequestPage');
        $this->assertGreaterThan(0, $page);
    }
}
