<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Utility\SettingsManager;
use Cake\Cache\Cache;

class RateLimitService
{
    public function enforceLimit(string $service = 'anthropic'): bool
    {
        if (!SettingsManager::read('AI.enableMetrics', true)) {
            return true;
        }

        $hourlyLimit = (int)SettingsManager::read('AI.hourlyLimit', 100);

        if ($hourlyLimit === 0) {
            return true; // Unlimited
        }

        $key = "rate_limit_{$service}_" . date('Y-m-d-H');
        $current = Cache::read($key) ?? 0;

        if ($current >= $hourlyLimit) {
            return false;
        }

        Cache::write($key, $current + 1, '+1 hour');

        return true;
    }

    public function getCurrentUsage(string $service = 'anthropic'): array
    {
        $key = "rate_limit_{$service}_" . date('Y-m-d-H');
        $current = Cache::read($key) ?? 0;
        $limit = (int)SettingsManager::read('AI.hourlyLimit', 100);

        return [
            'current' => $current,
            'limit' => $limit,
            'remaining' => $limit > 0 ? max(0, $limit - $current) : -1,
        ];
    }

    public function checkDailyCostLimit(float $todaysCost): bool
    {
        $dailyLimit = (float)SettingsManager::read('AI.dailyCostLimit', 2.50);

        return $dailyLimit === 0 || $todaysCost < $dailyLimit;
    }
}
