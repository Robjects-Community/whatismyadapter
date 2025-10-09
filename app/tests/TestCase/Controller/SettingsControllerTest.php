<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\SettingsController Test Case
 *
 * Auto-generated test file for SettingsController
 * Tests both authenticated and unauthenticated access scenarios
 *
 * @uses \App\Controller\SettingsController
 */
class SettingsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Settings'
    ];

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
     * Test index method - Authenticated access
     *
     * @return void
     */
    public function testIndexAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/settings');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test index method - Unauthenticated access
     *
     * @return void
     */
    public function testIndexUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/settings');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test view method - Authenticated access
     *
     * @return void
     */
    public function testViewAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            $this->get('/settings/view/' . $setting->id);
            // Smoke test: verify page responds successfully
            $this->assertResponseOk();
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

    /**
     * Test view method - Unauthenticated access
     *
     * @return void
     */
    public function testViewUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            $this->get('/settings/view/' . $setting->id);
            // Smoke test: verify page responds (may be 200 or 302 redirect)
            $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

    /**
     * Test add method - Authenticated access
     *
     * @return void
     */
    public function testAddAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/settings/add');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test add method - Unauthenticated access
     *
     * @return void
     */
    public function testAddUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/settings/add');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test edit method - Authenticated access
     *
     * @return void
     */
    public function testEditAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            $this->get('/settings/edit/' . $setting->id);
            // Smoke test: verify page responds successfully
            $this->assertResponseOk();
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

    /**
     * Test edit method - Unauthenticated access
     *
     * @return void
     */
    public function testEditUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            $this->get('/settings/edit/' . $setting->id);
            // Smoke test: verify page responds (may be 200 or 302 redirect)
            $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

    /**
     * Test delete method - Authenticated access
     *
     * @return void
     */
    public function testDeleteAuthenticated(): void
    {
        $this->enableCsrfToken();
        $this->mockAuthenticatedUser();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            // Delete requires POST method with CSRF token
            $this->post('/settings/delete/' . $setting->id);
            // Should redirect after delete
            $this->assertRedirect(['action' => 'index']);
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

    /**
     * Test delete method - Unauthenticated access
     *
     * @return void
     */
    public function testDeleteUnauthenticated(): void
    {
        $this->enableCsrfToken();
        $this->mockUnauthenticatedRequest();
        
        // Get a setting from fixtures to test with
        $settings = $this->getTableLocator()->get('Settings');
        $setting = $settings->find()->first();
        
        if ($setting) {
            // Delete requires POST method with CSRF token
            $this->post('/settings/delete/' . $setting->id);
            // Smoke test: verify page responds (may be 200 or 302 redirect)
            $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
        } else {
            $this->markTestSkipped('No settings found in fixtures');
        }
    }

}
