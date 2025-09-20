<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Exception;

/**
 * Enhanced Rate Limit Service with multi-service support
 * Fixes the dashboard counter update issues
 */
class RateLimitService
{
    private ?string $settingsManagerClass = null;

    /**
     * Constructor - allows dependency injection for testing
     */
    public function __construct(?string $settingsManagerClass = null)
    {
        $this->settingsManagerClass = $settingsManagerClass;
        $this->ensureRateLimitCacheConfig();
    }

    /**
     * Ensure dedicated cache configuration exists
     */
    private function ensureRateLimitCacheConfig(): void
    {
        if (!Cache::configured('rate_limit')) {
            Cache::setConfig('rate_limit', [
                'className' => 'File',
                'duration' => '+1 hour',
                'prefix' => 'rl_',
                'path' => CACHE . 'rate_limit' . DS,
                'serialize' => true,
            ]);
        }
    }

    /**
     * Read a setting value, using injected settings manager class if available
     */
    private function readSetting(string $key, mixed $default = null): mixed
    {
        if ($this->settingsManagerClass !== null) {
            $class = $this->settingsManagerClass;

            return $class::read($key, $default);
        }

        return SettingsManager::read($key, $default);
    }

    /**
     * Enforce rate limit with atomic increment
     */
    public function enforceLimit(string $service = 'anthropic'): bool
    {
        if (!$this->readSetting('AI.enableMetrics', true)) {
            return true;
        }

        $hourlyLimit = (int)$this->readSetting('AI.hourlyLimit', 100);

        if ($hourlyLimit === 0) {
            return true; // Unlimited
        }

        $key = "rate_limit_{$service}_" . date('Y-m-d-H');

        // Try atomic increment first
        try {
            // Use increment directly - CakePHP 5.x changed the cache API
            $current = Cache::increment($key, 1, 'rate_limit');
            if ($current === false) {
                // Key doesn't exist, initialize it
                Cache::write($key, 1, 'rate_limit');
                $current = 1;
            }

            return $current <= $hourlyLimit;
        } catch (Exception $e) {
            // Fall through to non-atomic approach
        }

        // Fallback to read-modify-write (less safe but compatible)
        $current = Cache::read($key, 'rate_limit') ?? 0;

        if ($current >= $hourlyLimit) {
            return false;
        }

        Cache::write($key, $current + 1, 'rate_limit');

        return true;
    }

    /**
     * Get current usage for a single service
     */
    public function getCurrentUsage(string $service = 'anthropic'): array
    {
        $key = "rate_limit_{$service}_" . date('Y-m-d-H');
        $current = Cache::read($key, 'rate_limit') ?? 0;
        $limit = (int)$this->readSetting('AI.hourlyLimit', 100);

        return [
            'current' => $current,
            'limit' => $limit,
            'remaining' => $limit > 0 ? max(0, $limit - $current) : -1,
        ];
    }

    /**
     * Get current usage for multiple services
     */
    public function getCurrentUsageForServices(array $services = ['anthropic', 'google']): array
    {
        $result = [];

        foreach ($services as $service) {
            $result[$service] = $this->getCurrentUsage($service);
        }

        return $result;
    }

    /**
     * Get combined usage across all services
     */
    public function getCombinedUsage(array $services = ['anthropic', 'google']): array
    {
        $totalCurrent = 0;
        $totalLimit = 0;

        foreach ($services as $service) {
            $usage = $this->getCurrentUsage($service);
            $totalCurrent += $usage['current'];
            $totalLimit += $usage['limit'];
        }

        return [
            'current' => $totalCurrent,
            'limit' => $totalLimit,
            'remaining' => $totalLimit > 0 ? max(0, $totalLimit - $totalCurrent) : -1,
        ];
    }

    /**
     * Check daily cost limit
     */
    public function checkDailyCostLimit(float $todaysCost): bool
    {
        $dailyLimit = (float)$this->readSetting('AI.dailyCostLimit', 50.00);

        return $dailyLimit === 0.0 || $todaysCost < $dailyLimit;
    }

    /**
     * Reset usage for a service (for testing)
     */
    public function resetUsage(string $service): void
    {
        $key = "rate_limit_{$service}_" . date('Y-m-d-H');
        Cache::delete($key, 'rate_limit');
    }

    /**
     * Get all active rate limit keys for debugging
     */
    public function getActiveKeys(): array
    {
        // Just return the expected keys for current hour
        // CakePHP 5.x changed the cache API and we don't need to check engine
        $hour = date('Y-m-d-H');

        return [
            "rate_limit_anthropic_{$hour}",
            "rate_limit_google_{$hour}",
        ];
    }
}
