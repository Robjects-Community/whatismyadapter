<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\PagesController Test Case
 *
 * Auto-generated test file for PagesController
 * Tests both authenticated and unauthenticated access scenarios
 *
 * @uses \App\Controller\PagesController
 */
class PagesControllerTest extends TestCase
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
        'app.Pages'
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
     * Test display method - Authenticated access
     *
     * @return void
     */
    public function testDisplayAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/pages/display');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test display method - Unauthenticated access
     *
     * @return void
     */
    public function testDisplayUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/pages/display');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

}
