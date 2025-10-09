<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\RateLimitService;
use App\TestSuite\Stub\SettingsManagerStub;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

// Load our stub manually since it's not autoloaded
require_once ROOT . DS . 'tests' . DS . 'TestSuite' . DS . 'Stub' . DS . 'SettingsManagerStub.php';

/**
 * App\Service\Api\RateLimitService Test Case
 */
class RateLimitServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure array cache for testing
        if (!Cache::configured('default')) {
            Cache::setConfig('default', ['className' => 'Array']);
        }
        if (!Cache::configured('rate_limit')) {
            Cache::setConfig('rate_limit', ['className' => 'Array']);
        }

        // Clear any existing cache data
        Cache::clear('default');
        Cache::clear('rate_limit');

        // Reset settings stub
        SettingsManagerStub::reset();

        // Ensure clean rate limit usage for services used in tests
        $svc = new RateLimitService(SettingsManagerStub::class);
        $svc->resetUsage('testsvc');
        $svc->resetUsage('testsvc2');
    }

    protected function tearDown(): void
    {
        // Clear cache data but don't drop the config
        Cache::clear('default');
        Cache::clear('rate_limit');

        parent::tearDown();
    }

    /**
     * Test getCurrentUsage baseline
     *
     * @return void
     */
    public function testGetCurrentUsageBaseline(): void
    {
        SettingsManagerStub::set('AI.hourlyLimit', 5);

        $service = new RateLimitService(SettingsManagerStub::class);
        $usage = $service->getCurrentUsage('testsvc');

        $this->assertEquals(0, $usage['current']);
        $this->assertEquals(5, $usage['limit']);
        $this->assertEquals(5, $usage['remaining']);
    }

    /**
     * Test enforceLimit increments and blocks after limit
     *
     * @return void
     */
    public function testEnforceLimitIncrementsAndBlocks(): void
    {
        SettingsManagerStub::set('AI.hourlyLimit', 2);

        $service = new RateLimitService(SettingsManagerStub::class);

        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertFalse($service->enforceLimit('testsvc')); // Should be blocked
    }

    /**
     * Test enforceLimit unlimited when limit is 0
     *
     * @return void
     */
    public function testEnforceLimitUnlimited(): void
    {
        SettingsManagerStub::set('AI.hourlyLimit', 0); // Unlimited

        $service = new RateLimitService(SettingsManagerStub::class);

        // Should allow many calls
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
    }

    /**
     * Test enforceLimit when metrics are disabled
     *
     * @return void
     */
    public function testEnforceLimitDisabledMetrics(): void
    {
        SettingsManagerStub::set('AI.enableMetrics', false);
        SettingsManagerStub::set('AI.hourlyLimit', 1); // Very restrictive limit

        $service = new RateLimitService(SettingsManagerStub::class);

        // Should always return true when metrics are disabled
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
        $this->assertTrue($service->enforceLimit('testsvc'));
    }

    /**
     * Test checkDailyCostLimit
     *
     * @return void
     */
    public function testCheckDailyCostLimit(): void
    {
        SettingsManagerStub::set('AI.dailyCostLimit', 10.00);

        $service = new RateLimitService(SettingsManagerStub::class);

        $this->assertTrue($service->checkDailyCostLimit(9.99));
        $this->assertFalse($service->checkDailyCostLimit(10.01));
        $this->assertFalse($service->checkDailyCostLimit(15.00));
    }

    /**
     * Test checkDailyCostLimit unlimited when limit is 0
     *
     * @return void
     */
    public function testCheckDailyCostLimitUnlimited(): void
    {
        SettingsManagerStub::set('AI.dailyCostLimit', 0); // Unlimited

        $service = new RateLimitService(SettingsManagerStub::class);

        // Should always return true when limit is 0
        $this->assertTrue($service->checkDailyCostLimit(100.00));
        $this->assertTrue($service->checkDailyCostLimit(1000.00));
    }

    /**
     * Test getCurrentUsage after increments
     *
     * @return void
     */
    public function testGetCurrentUsageAfterIncrements(): void
    {
        SettingsManagerStub::set('AI.hourlyLimit', 5);

        $service = new RateLimitService(SettingsManagerStub::class);

        // Use the service a few times
        $service->enforceLimit('testsvc2');
        $service->enforceLimit('testsvc2');

        $usage = $service->getCurrentUsage('testsvc2');

        $this->assertEquals(2, $usage['current']);
        $this->assertEquals(5, $usage['limit']);
        $this->assertEquals(3, $usage['remaining']);
    }
}
