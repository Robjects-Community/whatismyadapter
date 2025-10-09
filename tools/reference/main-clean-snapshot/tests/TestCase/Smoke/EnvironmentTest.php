<?php
declare(strict_types=1);

namespace App\Test\TestCase\Smoke;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Exception;

/**
 * Environment smoke test
 *
 * This test validates that the basic testing environment is working correctly
 * without requiring complex fixtures or database setup.
 */
class EnvironmentTest extends TestCase
{
    /**
     * Test that PHPUnit is working in the Docker container
     *
     * @return void
     */
    public function testPhpUnitIsWorking(): void
    {
        $this->assertTrue(true, 'PHPUnit is working correctly');
    }

    /**
     * Test that CakePHP application is configured for testing
     *
     * @return void
     */
    public function testCakePHPIsConfiguredForTesting(): void
    {
        $this->assertTrue(Configure::read('debug'), 'Debug mode should be enabled for testing');

        // Environment might be null if not explicitly set, that's okay
        $environment = Configure::read('App.environment');
        $this->assertTrue(
            $environment === 'test' || $environment === null,
            'Should be in test environment or unset (actual: ' . var_export($environment, true) . ')',
        );
    }

    /**
     * Test that database connection is available
     *
     * @return void
     */
    public function testDatabaseConnectionIsAvailable(): void
    {
        $connection = ConnectionManager::get('test');
        $this->assertNotNull($connection, 'Test database connection should be available');

        // Simple query to verify connection works
        $result = $connection->execute('SELECT 1 as test_value')->fetch();
        $this->assertNotEmpty($result, 'Database query should return a result');
        $this->assertEquals(1, $result[0] ?? $result['test_value'] ?? null, 'Database query should work');
    }

    /**
     * Test that Redis connection is available for queue/cache
     *
     * @return void
     */
    public function testRedisConnectionIsAvailable(): void
    {
        try {
            $cache = Cache::getConfig('default');
            $this->assertNotNull($cache, 'Default cache configuration should exist');

            // Try to write and read from cache
            Cache::write('test_key', 'test_value', 'default');
            $value = Cache::read('test_key', 'default');
            $this->assertEquals('test_value', $value, 'Cache read/write should work');

            // Clean up
            Cache::delete('test_key', 'default');
        } catch (Exception $e) {
            // If Redis is not available, that's okay for basic testing
            $this->markTestSkipped('Redis/Cache not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that essential directories are writable
     *
     * @return void
     */
    public function testDirectoriesAreWritable(): void
    {
        $directories = [
            TMP,
            LOGS,
            TMP . 'cache',
            TMP . 'tests',
        ];

        foreach ($directories as $directory) {
            $this->assertTrue(is_dir($directory), "Directory {$directory} should exist");
            $this->assertTrue(is_writable($directory), "Directory {$directory} should be writable");
        }
    }

    /**
     * Test that environment variables are loaded
     *
     * @return void
     */
    public function testEnvironmentVariablesAreLoaded(): void
    {
        // These should be set in the Docker environment
        $this->assertNotEmpty(env('TEST_DB_HOST'), 'TEST_DB_HOST should be set');
        $this->assertNotEmpty(env('TEST_DB_DATABASE'), 'TEST_DB_DATABASE should be set');
        $this->assertNotEmpty(env('TEST_DB_USERNAME'), 'TEST_DB_USERNAME should be set');
        $this->assertNotEmpty(env('TEST_DB_PASSWORD'), 'TEST_DB_PASSWORD should be set');
    }
}
