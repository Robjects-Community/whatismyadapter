<?php
declare(strict_types=1);

namespace DefaultTheme\Test\TestCase;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use DefaultTheme\DefaultThemePlugin;

/**
 * DefaultTheme\DefaultThemePlugin Test Case
 *
 * Tests the main plugin class functionality
 */
class DefaultThemePluginTest extends TestCase
{
    /**
     * Test plugin instance
     *
     * @var \DefaultTheme\DefaultThemePlugin
     */
    protected DefaultThemePlugin $plugin;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new DefaultThemePlugin();
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
     * Test that the plugin can be instantiated
     *
     * @return void
     */
    public function testPluginInstantiation(): void
    {
        $this->assertInstanceOf(
            DefaultThemePlugin::class,
            $this->plugin,
            'Plugin should be an instance of DefaultThemePlugin'
        );
    }

    /**
     * Test that the plugin name is correct
     *
     * @return void
     */
    public function testPluginName(): void
    {
        $this->assertEquals(
            'DefaultTheme',
            $this->plugin->getName(),
            'Plugin name should be "DefaultTheme"'
        );
    }

    /**
     * Test that the plugin path is correct
     *
     * @return void
     */
    public function testPluginPath(): void
    {
        $path = $this->plugin->getPath();
        $this->assertDirectoryExists($path, 'Plugin path should exist');
        $this->assertStringContainsString('DefaultTheme', $path, 'Plugin path should contain "DefaultTheme"');
    }

    /**
     * Test that the plugin config path exists
     *
     * @return void
     */
    public function testPluginConfigPath(): void
    {
        $configPath = $this->plugin->getConfigPath();
        $this->assertDirectoryExists($configPath, 'Plugin config path should exist');
    }

    /**
     * Test that the plugin class path exists
     *
     * @return void
     */
    public function testPluginClassPath(): void
    {
        $classPath = $this->plugin->getClassPath();
        $this->assertDirectoryExists($classPath, 'Plugin class path should exist');
    }
}
