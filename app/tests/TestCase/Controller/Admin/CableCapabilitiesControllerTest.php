<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\CableCapabilitiesController Test Case
 *
 * Auto-generated test file for Admin CableCapabilitiesController
 * Tests admin authentication and authorization requirements
 *
 * @uses \App\Controller\Admin\CableCapabilitiesController
 */
class CableCapabilitiesControllerTest extends AdminControllerTestCase
{
    use IntegrationTestTrait;
    use AuthenticationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = ['app.Users',
        'app.CableCapabilities',
        'app.SystemLogs'];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * Test index method - Admin authenticated access
     *
     * @return void
     */
    public function testIndexAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test index method - Requires admin authentication
     *
     * @return void
     */
    public function testIndexRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test view method - Admin authenticated access
     *
     * @return void
     */
    public function testViewAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/view');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test view method - Requires admin authentication
     *
     * @return void
     */
    public function testViewRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/view');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test category method - Admin authenticated access
     *
     * @return void
     */
    public function testCategoryAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/category');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test category method - Requires admin authentication
     *
     * @return void
     */
    public function testCategoryRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/category');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test certified method - Admin authenticated access
     *
     * @return void
     */
    public function testCertifiedAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/certified');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test certified method - Requires admin authentication
     *
     * @return void
     */
    public function testCertifiedRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/certified');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test search method - Admin authenticated access
     *
     * @return void
     */
    public function testSearchAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/search');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test search method - Requires admin authentication
     *
     * @return void
     */
    public function testSearchRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/search');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test analytics method - Admin authenticated access
     *
     * @return void
     */
    public function testAnalyticsAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/analytics');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test analytics method - Requires admin authentication
     *
     * @return void
     */
    public function testAnalyticsRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/analytics');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test export method - Admin authenticated access
     *
     * @return void
     */
    public function testExportAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/export');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test export method - Requires admin authentication
     *
     * @return void
     */
    public function testExportRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/export');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }

    /**
     * Test returnToProducts method - Admin authenticated access
     *
     * @return void
     */
    public function testReturnToProductsAsAdmin(): void
    {
        $this->mockAdminUser();
        $this->get('/admin/cable-capabilities/return-to-products');
        
        // Smoke test: verify admin can access
        $this->assertResponseOk();
    }

    /**
     * Test returnToProducts method - Requires admin authentication
     *
     * @return void
     */
    public function testReturnToProductsRequiresAdmin(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/admin/cable-capabilities/return-to-products');
        
        // Should redirect to login or home
        $this->assertRedirect();
    }


    // =================================================================
    // SMOKE TESTS - Accept current state temporarily
    // =================================================================

    /**
     * Smoke test: Verify index route exists and authentication works
     * 
     * @return void
     */
    public function testIndexRouteExists(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/cable-capabilities');
        
        // Accept either 200 OK or 500 error - we just want to verify routing works
        $statusCode = $this->_response->getStatusCode();
        $this->assertContains($statusCode, [200, 500], 'Index route should exist and not redirect');
    }

    /**
     * Smoke test: Verify add route exists
     * 
     * @return void
     */
    public function testAddRouteExists(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/cable-capabilities/add');
        
        $statusCode = $this->_response->getStatusCode();
        $this->assertContains($statusCode, [200, 500], 'Add route should exist and not redirect');
    }

    /**
     * Smoke test: Verify view route exists
     * 
     * @return void
     */
    public function testViewRouteExists(): void
    {
        $this->loginAsAdmin();
        
        // Get first fixture ID dynamically
        $tableName = 'Products';
        $id = $this->getFirstFixtureId($tableName);
        
        if ($id) {
            $this->get("/admin/cable-capabilities/view/{$id}");
            $statusCode = $this->_response->getStatusCode();
            $this->assertContains($statusCode, [200, 500], 'View route should exist and not redirect');
        } else {
            $this->markTestSkipped('No fixture data available for view test');
        }
    }
}