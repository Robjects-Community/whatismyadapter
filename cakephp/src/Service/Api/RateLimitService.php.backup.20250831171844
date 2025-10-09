<?php
declare(strict_types=1);
namespace App\Service\Api;
use Cake\Cache\Cache;
use App\Utility\SettingsManager;

class RateLimitService
{
    private ?string $settingsManagerClass = null;
    
    /**
     * Constructor - allows dependency injection for testing
     */
    public function __construct(?string $settingsManagerClass = null)
    {
        $this->settingsManagerClass = $settingsManagerClass;
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
        $current = Cache::read($key) ?? 0;
        
        if ($current >= $hourlyLimit) {
            return false;
        }
        
        Cache::write($key, $current + 1); // Will use default TTL from cache config
        return true;
    }
    
    public function getCurrentUsage(string $service = 'anthropic'): array
    {
        $key = "rate_limit_{$service}_" . date('Y-m-d-H');
        $current = Cache::read($key) ?? 0;
        $limit = (int)$this->readSetting('AI.hourlyLimit', 100);
        
        return [
            'current' => $current,
            'limit' => $limit,
            'remaining' => $limit > 0 ? max(0, $limit - $current) : -1,
        ];
    }
    
    public function checkDailyCostLimit(float $todaysCost): bool
    {
        $dailyLimit = (float)$this->readSetting('AI.dailyCostLimit', 50.00);
        return $dailyLimit === 0.0 || $todaysCost < $dailyLimit;
    }
}
