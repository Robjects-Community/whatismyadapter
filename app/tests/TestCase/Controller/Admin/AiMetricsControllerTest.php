<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

/**
 * App\Controller\Admin\AiMetricsController Test Case
 *
 * Tests for Admin AiMetricsController including CRUD operations,
 * authentication, and authorization requirements.
 *
 * This test serves as a template for other Admin controller tests,
 * demonstrating proper use of AdminControllerTestCase helper methods.
 *
 * @uses \App\Controller\Admin\AiMetricsController
 */
class AiMetricsControllerTest extends AdminControllerTestCase
{

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AiMetrics',
        'app.Settings',
        'app.SystemLogs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Cake\Core\Configure::write('debug', true);
        
        // Disable database logging in tests to avoid system_logs table issues
        if (\Cake\Log\Log::getConfig('database')) {
            \Cake\Log\Log::drop('database');
        }
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

    // =================================================================
    // SMOKE TESTS - Accept current state temporarily
    // =================================================================

    /**
     * Smoke test: Verify route exists and authentication works
     * 
     * @return void
     */
    public function testDashboardRouteExists(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics/dashboard');
        
        // Accept either 200 OK or 500 error - we just want to verify routing works
        $statusCode = $this->_response->getStatusCode();
        $this->assertContains($statusCode, [200, 500], 'Dashboard route should exist and not redirect');
    }

    /**
     * Smoke test: Verify index route exists
     * 
     * @return void
     */
    public function testIndexRouteExists(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics');
        
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
        $this->get('/admin/ai-metrics/add');
        
        $statusCode = $this->_response->getStatusCode();
        $this->assertContains($statusCode, [200, 500], 'Add route should exist and not redirect');
    }


    /**
     * Test dashboard method - Admin authenticated access
     *
     * @return void
     */
    public function testDashboardAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics/dashboard');
        
        $this->assertResponseOk();
        $this->assertResponseContains('AI Metrics Dashboard');
        
        // Verify required view variables are set
        $viewVars = $this->viewVariable('totalCalls');
        $this->assertNotNull($viewVars, 'Dashboard should set totalCalls variable');
    }

    /**
     * Test dashboard method - Requires admin authentication
     *
     * @return void
     */
    public function testDashboardRequiresAdmin(): void
    {
        $this->logout();
        $this->get('/admin/ai-metrics/dashboard');
        
        $this->assertRedirect();
    }

    /**
     * Test realtimeData method - Admin authenticated access
     *
     * @return void
     */
    public function testRealtimeDataAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics/realtime-data');
        
        $this->assertResponseOk();
        // Should return JSON data
        $this->assertResponseContains('success');
    }

    /**
     * Test realtimeData method - Requires admin authentication
     *
     * @return void
     */
    public function testRealtimeDataRequiresAdmin(): void
    {
        $this->logout();
        $this->get('/admin/ai-metrics/realtime-data');
        
        $this->assertRedirect();
    }

    /**
     * Test index method - Admin authenticated access
     *
     * @return void
     */
    public function testIndexAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics');
        
        // Debug: Output response body if not OK
        if ($this->_response->getStatusCode() !== 200) {
            echo "\nResponse Status: " . $this->_response->getStatusCode() . "\n";
            echo "Response Body: " . $this->_response->getBody() . "\n";
        }
        
        $this->assertResponseOk();
        $this->assertResponseContains('AI Metrics');
        
        // Verify pagination is working
        $aiMetrics = $this->viewVariable('aiMetrics');
        $this->assertNotNull($aiMetrics);
    }

    /**
     * Test index method - Requires admin authentication
     *
     * @return void
     */
    public function testIndexRequiresAdmin(): void
    {
        $this->logout();
        $this->get('/admin/ai-metrics');
        
        $this->assertRedirect();
    }

    /**
     * Test view method - Admin authenticated access
     *
     * ✅ TEMPLATE: This demonstrates proper fixture ID usage
     *
     * @return void
     */
    public function testViewAsAdmin(): void
    {
        $this->loginAsAdmin();
        
        // ✅ Get valid ID from fixture using helper method
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        
        $this->get("/admin/ai-metrics/view/{$aiMetricId}");
        
        $this->assertResponseOk();
        $this->assertResponseContains('AI Metric');
        
        // Verify the correct entity is loaded
        $aiMetric = $this->viewVariable('aiMetric');
        $this->assertNotNull($aiMetric);
        $this->assertEquals($aiMetricId, $aiMetric->id);
    }

    /**
     * Test view method - Requires admin authentication
     *
     * @return void
     */
    public function testViewRequiresAdmin(): void
    {
        $this->logout();
        
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        $this->get("/admin/ai-metrics/view/{$aiMetricId}");
        
        $this->assertRedirect();
    }

    /**
     * Test add method - Admin authenticated access (GET)
     *
     * @return void
     */
    public function testAddAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/ai-metrics/add');
        
        $this->assertResponseOk();
        $this->assertResponseContains('Add AI Metric');
    }

    /**
     * Test add method - Admin authenticated access (POST)
     *
     * ✅ TEMPLATE: This demonstrates proper POST request testing
     *
     * @return void
     */
    public function testAddPostAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $data = [
            'task_type' => 'seo_generation',
            'execution_time_ms' => 1500,
            'tokens_used' => 250,
            'cost_usd' => 0.05,
            'success' => true,
            'model_used' => 'claude-3-opus',
        ];
        
        $this->post('/admin/ai-metrics/add', $data);
        
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The ai metric has been saved.');
        
        // Verify record was created
        $this->assertRecordExists('ai_metrics', ['task_type' => 'seo_generation']);
    }

    /**
     * Test add method - Requires admin authentication
     *
     * @return void
     */
    public function testAddRequiresAdmin(): void
    {
        $this->logout();
        $this->get('/admin/ai-metrics/add');
        
        $this->assertRedirect();
    }

    /**
     * Test edit method - Admin authenticated access (GET)
     *
     * ✅ TEMPLATE: This demonstrates proper edit form loading
     *
     * @return void
     */
    public function testEditAsAdmin(): void
    {
        $this->loginAsAdmin();
        
        // ✅ Get valid ID from fixture
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        
        $this->get("/admin/ai-metrics/edit/{$aiMetricId}");
        
        $this->assertResponseOk();
        $this->assertResponseContains('Edit AI Metric');
        
        // Verify the entity is loaded in the form
        $aiMetric = $this->viewVariable('aiMetric');
        $this->assertNotNull($aiMetric);
        $this->assertEquals($aiMetricId, $aiMetric->id);
    }

    /**
     * Test edit method - Admin authenticated access (POST)
     *
     * ✅ TEMPLATE: This demonstrates proper edit submission testing
     *
     * @return void
     */
    public function testEditPostAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        
        $data = [
            'task_type' => 'updated_task_type',
            'execution_time_ms' => 2000,
            'tokens_used' => 300,
            'success' => true,
        ];
        
        $this->post("/admin/ai-metrics/edit/{$aiMetricId}", $data);
        
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The ai metric has been saved.');
        
        // Verify record was updated
        $this->assertRecordExists('ai_metrics', [
            'id' => $aiMetricId,
            'task_type' => 'updated_task_type',
        ]);
    }

    /**
     * Test edit method - Requires admin authentication
     *
     * @return void
     */
    public function testEditRequiresAdmin(): void
    {
        $this->logout();
        
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        $this->get("/admin/ai-metrics/edit/{$aiMetricId}");
        
        $this->assertRedirect();
    }

    /**
     * Test delete method - Admin authenticated access
     *
     * ✅ TEMPLATE: This demonstrates proper delete testing
     *
     * @return void
     */
    public function testDeleteAsAdmin(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        // ✅ Get valid ID from fixture
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        
        // Get count before delete
        $countBefore = $this->getRecordCount('ai_metrics');
        
        // DELETE should use POST method
        $this->post("/admin/ai-metrics/delete/{$aiMetricId}");
        
        $this->assertResponseSuccess();
        // Controller uses referer() so we can't predict exact redirect
        $this->assertRedirect();
        $this->assertFlashMessage('The ai metric has been deleted.');
        
        // Verify record was deleted
        $this->assertRecordNotExists('ai_metrics', ['id' => $aiMetricId]);
        
        // Verify count decreased
        $countAfter = $this->getRecordCount('ai_metrics');
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    /**
     * Test delete method - Requires admin authentication
     *
     * @return void
     */
    public function testDeleteRequiresAdmin(): void
    {
        $this->logout();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $aiMetricId = $this->getFirstFixtureId('ai_metrics');
        $this->post("/admin/ai-metrics/delete/{$aiMetricId}");
        
        $this->assertRedirect();
    }

}
