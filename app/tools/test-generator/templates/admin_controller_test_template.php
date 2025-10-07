<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\{{CONTROLLER_NAME}}Controller Test Case
 *
 * Auto-generated test file for Admin {{CONTROLLER_NAME}}Controller
 * Tests admin authentication and authorization requirements
 *
 * @uses \App\Controller\Admin\{{CONTROLLER_NAME}}Controller
 */
class {{CONTROLLER_NAME}}ControllerTest extends TestCase
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
        {{ADDITIONAL_FIXTURES}}
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

{{TEST_METHODS}}
}
