<?php
declare(strict_types=1);

namespace AdminTheme\Test\TestCase\Controller;

use AdminTheme\Controller\AppController;
use Cake\TestSuite\TestCase;

/**
 * AdminTheme\Controller\AppController Test Case
 */
class AppControllerTest extends TestCase
{
    /**
     * Subject under test
     *
     * @var \AdminTheme\Controller\AppController
     */
    protected $AppController;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create controller instance
        $this->AppController = new AppController();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AppController);
        parent::tearDown();
    }

    /**
     * Test controller instantiation
     *
     * @return void
     */
    public function testControllerInstantiation(): void
    {
        $this->assertInstanceOf(AppController::class, $this->AppController);
        $this->assertInstanceOf(\App\Controller\AppController::class, $this->AppController);
    }

    /**
     * Test that controller extends base AppController
     *
     * @return void
     */
    public function testExtendsBaseController(): void
    {
        $this->assertTrue($this->AppController instanceof \App\Controller\AppController);
    }
}
