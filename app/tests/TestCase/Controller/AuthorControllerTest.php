<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\AuthorController Test Case
 *
 * Auto-generated test file for AuthorController
 * Tests both authenticated and unauthenticated access scenarios
 *
 * @uses \App\Controller\AuthorController
 */
class AuthorControllerTest extends TestCase
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
        'app.Author'
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
     * Test about method - Authenticated access
     *
     * @return void
     */
    public function testAboutAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/author/about');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test about method - Unauthenticated access
     *
     * @return void
     */
    public function testAboutUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/author/about');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test hireMe method - Authenticated access
     *
     * @return void
     */
    public function testHireMeAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/author/hire-me');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test hireMe method - Unauthenticated access
     *
     * @return void
     */
    public function testHireMeUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/author/hire-me');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test social method - Authenticated access
     *
     * @return void
     */
    public function testSocialAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/author/social');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test social method - Unauthenticated access
     *
     * @return void
     */
    public function testSocialUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/author/social');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

    /**
     * Test github method - Authenticated access
     *
     * @return void
     */
    public function testGithubAuthenticated(): void
    {
        $this->mockAuthenticatedUser();
        $this->get('/author/github');
        
        // Smoke test: verify page responds successfully
        $this->assertResponseOk();
    }

    /**
     * Test github method - Unauthenticated access
     *
     * @return void
     */
    public function testGithubUnauthenticated(): void
    {
        $this->mockUnauthenticatedRequest();
        $this->get('/author/github');
        
        // Smoke test: verify page responds (may be 200 or 302 redirect)
        $this->assertResponseCodeIn([200, 302], 'Response should be OK or redirect');
    }

}
