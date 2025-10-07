<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api;

use App\Service\Api\RateLimitService;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

class RateLimitServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::setConfig('rate_limit', [
            'className' => 'Array',
            'prefix' => 'test_rl_',
            'serialize' => true,
        ]);
    }

    public function testEnforceLimitRespectsHourlyLimit(): void
    {
        // Fake settings manager returning metrics enabled and small hourly limit
        // phpcs:disable
        eval('namespace App\\Test\\TestCase\\Service\\Api { class FakeSettings { public static function read($k,$d=null){ return match($k){ "AI.enableMetrics"=>true, "AI.hourlyLimit"=>2, default=>$d }; } } }');
        // phpcs:enable

        $svc = new RateLimitService(\App\Test\TestCase\Service\Api\FakeSettings::class);

        $this->assertTrue($svc->enforceLimit('anthropic'));
        $this->assertTrue($svc->enforceLimit('anthropic'));
        // Third should exceed limit
        $this->assertFalse($svc->enforceLimit('anthropic'));

        $usage = $svc->getCurrentUsage('anthropic');
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
