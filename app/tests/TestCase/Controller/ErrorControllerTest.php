<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ErrorController Test Case
 *
 * Auto-generated test file for ErrorController
 * Tests both authenticated and unauthenticated access scenarios
 *
 * @uses \App\Controller\ErrorController
 */
class ErrorControllerTest extends TestCase
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
     * Test viewClasses method - Authenticated access
     *
     * @return void
     */
    public function testViewClassesAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/error/view-classes');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test viewClasses method - Unauthenticated access
     *
     * @return void
     */
    public function testViewClassesUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/error/view-classes');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

}
