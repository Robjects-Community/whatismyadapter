<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\RateLimitService;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

class RateLimitServiceTest extends TestCase
{
    // No special setup required

    public function testEnforceLimitRespectsHourlyLimit(): void
    {
        // Fake settings manager returning metrics enabled and small hourly limit
        // phpcs:disable
        eval('namespace App\\Test\\TestCase\\Service\\Api { class FakeSettings { public static function read($k,$d=null){ return match($k){ "AI.enableMetrics"=>true, "AI.hourlyLimit"=>2, default=>$d }; } } }');
        // phpcs:enable

        $svc = new RateLimitService(\App\Test\TestCase\Service\Api\FakeSettings::class);
        // Use a unique service key to avoid collisions with other tests
        $service = 'anthropic_' . substr(md5((string)microtime(true)), 0, 6);
        $svc->resetUsage($service);

        $this->assertTrue($svc->enforceLimit($service));
        $this->assertTrue($svc->enforceLimit($service));
        // Third should exceed limit
        $this->assertFalse($svc->enforceLimit($service));

        $usage = $svc->getCurrentUsage($service);
        $this->assertSame(2, $usage['limit']);
        $this->assertGreaterThanOrEqual(2, $usage['current']);
    }

    public function testGetCombinedUsageAggregatesServices(): void
    {
        // Fake settings
        // phpcs:disable
        eval('namespace App\\Test\\TestCase\\Service\\Api { class FakeSettings2 { public static function read($k,$d=null){ return match($k){ "AI.enableMetrics"=>true, "AI.hourlyLimit"=>5, default=>$d }; } } }');
        // phpcs:enable

        $svc = new RateLimitService(\App\Test\TestCase\Service\Api\FakeSettings2::class);

        // Ensure clean slate for this test
        $svc->resetUsage('anthropic');
        $svc->resetUsage('google');

        // Simulate some usage
        $svc->enforceLimit('anthropic');
        $svc->enforceLimit('google');
        $svc->enforceLimit('google');

        $combined = $svc->getCombinedUsage(['anthropic','google']);
        $this->assertSame(10, $combined['limit']);
        $this->assertEquals(3, $combined['current']);
        $this->assertEquals(7, $combined['remaining']);
    }
}
