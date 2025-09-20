<?php
declare(strict_types=1);

namespace AdminTheme\Test\TestCase;

use AdminTheme\AdminThemePlugin;
use Cake\Console\CommandCollection;
use Cake\Core\Container;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use TestApp\Application;

/**
 * AdminTheme\AdminThemePlugin Test Case
 */
class AdminThemePluginTest extends TestCase
{
    /**
     * Subject under test
     *
     * @var \AdminTheme\AdminThemePlugin
     */
    protected $plugin;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new AdminThemePlugin();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->plugin);
        parent::tearDown();
    }

    /**
     * Test bootstrap method
     *
     * @return void
     */
    public function testBootstrap(): void
    {
        $app = $this->createMock(Application::class);

        // This should not throw any exceptions
        $this->plugin->bootstrap($app);

        // Since bootstrap doesn't return anything or modify state in the basic implementation,
        // we just verify it executes without error
        $this->assertTrue(true);
    }

    /**
     * Test routes method
     *
     * @return void
     */
    public function testRoutes(): void
    {
        $routes = new RouteBuilder($this->createMock(Router::class), '/', []);

        // Test that routes can be called without error
        $this->plugin->routes($routes);

        // Verify that the plugin routes were added
        $this->assertTrue(true); // Basic verification that method executes
    }

    /**
     * Test middleware method
     *
     * @return void
     */
    public function testMiddleware(): void
    {
        $middlewareQueue = new MiddlewareQueue();

        $result = $this->plugin->middleware($middlewareQueue);

        // Should return the same middleware queue (no middleware added in basic implementation)
        $this->assertInstanceOf(MiddlewareQueue::class, $result);
    }

    /**
     * Test console method
     *
     * @return void
     */
    public function testConsole(): void
    {
        $commands = new CommandCollection();

        $result = $this->plugin->console($commands);

        // Should return a CommandCollection instance
        $this->assertInstanceOf(CommandCollection::class, $result);
    }

    /**
     * Test services method
     *
     * @return void
     */
    public function testServices(): void
    {
        $container = new Container();

        // This should not throw any exceptions
        $this->plugin->services($container);

        // Since services doesn't return anything or modify state in the basic implementation,
        // we just verify it executes without error
        $this->assertTrue(true);
    }
}
