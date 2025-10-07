<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\HealthController Test Case
 *
 * Auto-generated test file for HealthController
 * Tests both authenticated and unauthenticated access scenarios
 *
 * @uses \App\Controller\HealthController
 */
class HealthControllerTest extends TestCase
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
     * Test healthz method - Unauthenticated access
     *
     * @return void
     */
    public function testHealthzUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/health/healthz');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test readyz method - Unauthenticated access
     *
     * @return void
     */
    public function testReadyzUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/health/readyz');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

}
