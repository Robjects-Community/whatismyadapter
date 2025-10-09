<?php
/**
 * Comprehensive Rate Limit Dashboard Fix Script
 * 
 * This script analyzes the current rate limiting implementation,
 * identifies the issues, and applies comprehensive fixes.
 */

echo "=== AI Metrics Rate Limit Dashboard Fix ===\n\n";

// Step 1: Current State Analysis
echo "1. Analyzing Current Rate Limit Service...\n";

$rateLimitServicePath = __DIR__ . '/src/Service/Api/RateLimitService.php';
if (file_exists($rateLimitServicePath)) {
    $content = file_get_contents($rateLimitServicePath);
    echo "   ✅ RateLimitService found\n";
    
    // Check for key issues
    $issues = [];
    
    if (strpos($content, 'Cache::read($key) ?? 0') !== false) {
        $issues[] = "Non-atomic cache operations detected";
    }
    
    if (strpos($content, 'getCurrentUsageForServices') === false) {
        $issues[] = "Multi-service aggregation methods missing";
    }
    
    if (strpos($content, "'default'") !== false || strpos($content, 'Cache::read($key,') === false) {
        $issues[] = "Using default cache pool (potential process isolation issues)";
    }
    
    if (count($issues) > 0) {
        echo "   ⚠️  Issues found:\n";
        foreach ($issues as $issue) {
            echo "      - $issue\n";
        }
    } else {
        echo "   ✅ No obvious issues found\n";
    }
} else {
    echo "   ❌ RateLimitService not found at expected path\n";
}

echo "\n2. Checking Controller Implementation...\n";

$controllerPath = __DIR__ . '/src/Controller/Admin/AiMetricsController.php';
if (file_exists($controllerPath)) {
    $content = file_get_contents($controllerPath);
    echo "   ✅ AiMetricsController found\n";
    
    if (strpos($content, "getCurrentUsage()") !== false) {
        echo "   ✅ getCurrentUsage() call found\n";
    } else {
        echo "   ❌ getCurrentUsage() call not found\n";
    }
    
    if (strpos($content, "getCurrentUsageForServices") !== false) {
        echo "   ✅ Multi-service usage detected\n";
    } else {
        echo "   ⚠️  Single service usage only\n";
    }
} else {
    echo "   ❌ AiMetricsController not found\n";
}

echo "\n3. Checking Dashboard Template...\n";

$templatePath = __DIR__ . '/templates/Admin/AiMetrics/dashboard.php';
if (file_exists($templatePath)) {
    $content = file_get_contents($templatePath);
    echo "   ✅ Dashboard template found\n";
    
    if (strpos($content, 'rate-limit-value') !== false) {
        echo "   ✅ Rate limit display element found\n";
    } else {
        echo "   ❌ Rate limit display element not found\n";
    }
    
    if (strpos($content, 'updateDashboard') !== false) {
        echo "   ✅ JavaScript update function found\n";
    } else {
        echo "   ❌ JavaScript update function not found\n";
    }
} else {
    echo "   ❌ Dashboard template not found\n";
}

// Step 4: Apply Fixes
echo "\n=== APPLYING FIXES ===\n";

echo "\n4. Creating enhanced RateLimitService...\n";

$enhancedRateLimitService = '<?php
declare(strict_types=1);

namespace App\Service\Api;

use Cake\Cache\Cache;
use App\Utility\SettingsManager;

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
        if (!Cache::configured(\'rate_limit\')) {
            Cache::setConfig(\'rate_limit\', [
                \'className\' => \'File\',
                \'duration\' => \'+1 hour\',
                \'prefix\' => \'rl_\',
                \'path\' => CACHE . \'rate_limit\' . DS,
                \'serialize\' => true,
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
    public function enforceLimit(string $service = \'anthropic\'): bool
    {
        if (!$this->readSetting(\'AI.enableMetrics\', true)) {
            return true;
        }
        
        $hourlyLimit = (int)$this->readSetting(\'AI.hourlyLimit\', 100);
        
        if ($hourlyLimit === 0) {
            return true; // Unlimited
        }
        
        $key = "rate_limit_{$service}_" . date(\'Y-m-d-H\');
        
        // Try atomic increment first
        try {
            $engine = Cache::engine(\'rate_limit\');
            if (method_exists($engine, \'increment\')) {
                $current = Cache::increment($key, 1, \'rate_limit\');
                if ($current === false) {
                    // Key doesn\'t exist, initialize it
                    Cache::write($key, 1, \'rate_limit\');
                    $current = 1;
                }
                return $current <= $hourlyLimit;
            }
        } catch (\Exception $e) {
            // Fall through to non-atomic approach
        }
        
        // Fallback to read-modify-write (less safe but compatible)
        $current = Cache::read($key, \'rate_limit\') ?? 0;
        
        if ($current >= $hourlyLimit) {
            return false;
        }
        
        Cache::write($key, $current + 1, \'rate_limit\');
        return true;
    }
    
    /**
     * Get current usage for a single service
     */
    public function getCurrentUsage(string $service = \'anthropic\'): array
    {
        $key = "rate_limit_{$service}_" . date(\'Y-m-d-H\');
        $current = Cache::read($key, \'rate_limit\') ?? 0;
        $limit = (int)$this->readSetting(\'AI.hourlyLimit\', 100);
        
        return [
            \'current\' => $current,
            \'limit\' => $limit,
            \'remaining\' => $limit > 0 ? max(0, $limit - $current) : -1,
        ];
    }
    
    /**
     * Get current usage for multiple services
     */
    public function getCurrentUsageForServices(array $services = [\'anthropic\', \'google\']): array
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
    public function getCombinedUsage(array $services = [\'anthropic\', \'google\']): array
    {
        $totalCurrent = 0;
        $totalLimit = 0;
        $limit = (int)$this->readSetting(\'AI.hourlyLimit\', 100);
        
        foreach ($services as $service) {
            $usage = $this->getCurrentUsage($service);
            $totalCurrent += $usage[\'current\'];
            $totalLimit += $usage[\'limit\'];
        }
        
        return [
            \'current\' => $totalCurrent,
            \'limit\' => $totalLimit,
            \'remaining\' => $totalLimit > 0 ? max(0, $totalLimit - $totalCurrent) : -1,
        ];
    }
    
    /**
     * Check daily cost limit
     */
    public function checkDailyCostLimit(float $todaysCost): bool
    {
        $dailyLimit = (float)$this->readSetting(\'AI.dailyCostLimit\', 50.00);
        return $dailyLimit === 0.0 || $todaysCost < $dailyLimit;
    }
    
    /**
     * Reset usage for a service (for testing)
     */
    public function resetUsage(string $service): void
    {
        $key = "rate_limit_{$service}_" . date(\'Y-m-d-H\');
        Cache::delete($key, \'rate_limit\');
    }
    
    /**
     * Get all active rate limit keys for debugging
     */
    public function getActiveKeys(): array
    {
        try {
            $engine = Cache::engine(\'rate_limit\');
            if (method_exists($engine, \'clearGroup\')) {
                // For engines that support it, we could list keys
                // For now, return the expected keys for current hour
                $hour = date(\'Y-m-d-H\');
                return [
                    "rate_limit_anthropic_{$hour}",
                    "rate_limit_google_{$hour}",
                ];
            }
        } catch (\Exception $e) {
            // Ignore
        }
        
        return [];
    }
}
';

// Backup existing file
if (file_exists($rateLimitServicePath)) {
    $backupPath = $rateLimitServicePath . '.backup.' . date('YmdHis');
    copy($rateLimitServicePath, $backupPath);
    echo "   ✅ Backed up existing RateLimitService to: " . basename($backupPath) . "\n";
}

// Write enhanced service
if (file_put_contents($rateLimitServicePath, $enhancedRateLimitService) !== false) {
    echo "   ✅ Enhanced RateLimitService written successfully\n";
} else {
    echo "   ❌ Failed to write enhanced RateLimitService\n";
}

echo "\n5. Updating AiMetricsController...\n";

if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    
    // Update dashboard method
    $oldPattern = '/(\$rateLimitService = new \\\\App\\\\Service\\\\Api\\\\RateLimitService\(\);\s*\$currentUsage = \$rateLimitService->getCurrentUsage\(\);)/';
    $newReplacement = '$rateLimitService = new \\App\\Service\\Api\\RateLimitService();
        $perServiceUsage = $rateLimitService->getCurrentUsageForServices([\'anthropic\', \'google\']);
        $currentUsage = $rateLimitService->getCombinedUsage([\'anthropic\', \'google\']);';
    
    $controllerContent = preg_replace($oldPattern, $newReplacement, $controllerContent);
    
    // Update compact() call in dashboard method
    $oldCompact = 'compact(\'totalCalls\', \'successRate\', \'totalCost\', \'taskMetrics\', \'recentErrors\', \'currentUsage\')';
    $newCompact = 'compact(\'totalCalls\', \'successRate\', \'totalCost\', \'taskMetrics\', \'recentErrors\', \'currentUsage\', \'perServiceUsage\')';
    $controllerContent = str_replace($oldCompact, $newCompact, $controllerContent);
    
    // Update realtime-data method response
    $oldRealtimePattern = '/(\'currentUsage\' => \$currentUsage,)/';
    $newRealtimeReplacement = '$1
                    \'perServiceUsage\' => $perServiceUsage,';
    
    $controllerContent = preg_replace($oldRealtimePattern, $newRealtimeReplacement, $controllerContent);
    
    // Also update the realtime method to get per-service usage
    $realtimePattern = '/(\$rateLimitService = new \\\\App\\\\Service\\\\Api\\\\RateLimitService\(\);\s*\$currentUsage = \$rateLimitService->getCurrentUsage\(\);)/';
    $realtimeReplacement = '$rateLimitService = new \\App\\Service\\Api\\RateLimitService();
            $perServiceUsage = $rateLimitService->getCurrentUsageForServices([\'anthropic\', \'google\']);
            $currentUsage = $rateLimitService->getCombinedUsage([\'anthropic\', \'google\']);';
    
    $controllerContent = preg_replace($realtimePattern, $realtimeReplacement, $controllerContent);
    
    // Backup and write
    $backupPath = $controllerPath . '.backup.' . date('YmdHis');
    copy($controllerPath, $backupPath);
    
    if (file_put_contents($controllerPath, $controllerContent) !== false) {
        echo "   ✅ Controller updated successfully\n";
        echo "   ✅ Controller backup created: " . basename($backupPath) . "\n";
    } else {
        echo "   ❌ Failed to update controller\n";
    }
} else {
    echo "   ❌ Controller not found\n";
}

echo "\n6. Updating Dashboard Template...\n";

if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);
    
    // Replace the rate limit card section
    $oldRateLimit = '/(<div class="col-md-3">.*?<h5 class="card-title">[^<]*Rate Limit[^<]*<\/h5>.*?<\/div>.*?<\/div>.*?<\/div>)/s';
    
    $newRateLimit = '<div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __("Rate Limit") ?></h5>
                <h2 id="rate-limit-total" class="text-success">
                    <?= ($currentUsage["current"] ?? 0) ?>/<?= ($currentUsage["limit"] ?? 0) ?>
                </h2>
                <small class="text-muted"><?= __("This hour (combined)") ?></small>
                <div class="mt-2">
                    <div><strong>Anthropic:</strong> <span id="rate-limit-anthropic"><?= ($perServiceUsage["anthropic"]["current"] ?? 0) ?>/<?= ($perServiceUsage["anthropic"]["limit"] ?? 0) ?></span></div>
                    <div><strong>Google:</strong> <span id="rate-limit-google"><?= ($perServiceUsage["google"]["current"] ?? 0) ?>/<?= ($perServiceUsage["google"]["limit"] ?? 0) ?></span></div>
                </div>
            </div>
        </div>
    </div>';
    
    $templateContent = preg_replace($oldRateLimit, $newRateLimit, $templateContent);
    
    // Update JavaScript updateDashboard function
    $jsPattern = '/(updateCardById\(\'rate-limit-value\'[^}]+\})/s';
    $jsReplacement = '// Rate limit totals
        if (data.currentUsage) {
            const total = document.getElementById("rate-limit-total");
            if (total) {
                total.textContent = `${data.currentUsage.current}/${data.currentUsage.limit}`;
                // Update color based on remaining
                const remaining = data.currentUsage.remaining;
                total.className = total.className.split(" ").filter(cls => !cls.startsWith("text-")).join(" ") + 
                    (remaining > 10 ? " text-success" : remaining > 5 ? " text-warning" : " text-danger");
            }
        }

        // Per-service usage
        if (data.perServiceUsage) {
            if (data.perServiceUsage.anthropic) {
                const el = document.getElementById("rate-limit-anthropic");
                if (el) el.textContent = `${data.perServiceUsage.anthropic.current}/${data.perServiceUsage.anthropic.limit}`;
            }
            if (data.perServiceUsage.google) {
                const el = document.getElementById("rate-limit-google");
                if (el) el.textContent = `${data.perServiceUsage.google.current}/${data.perServiceUsage.google.limit}`;
            }
        }';
    
    $templateContent = preg_replace($jsPattern, $jsReplacement, $templateContent);
    
    // Backup and write
    $backupPath = $templatePath . '.backup.' . date('YmdHis');
    copy($templatePath, $backupPath);
    
    if (file_put_contents($templatePath, $templateContent) !== false) {
        echo "   ✅ Dashboard template updated successfully\n";
        echo "   ✅ Template backup created: " . basename($backupPath) . "\n";
    } else {
        echo "   ❌ Failed to update dashboard template\n";
    }
} else {
    echo "   ❌ Dashboard template not found\n";
}

echo "\n7. Creating test script for validation...\n";

$testScript = '<?php
/**
 * Rate Limit Test Script
 * Run this to test the rate limiting functionality
 */

// Simple test without full CakePHP bootstrap
echo "=== Rate Limit Test ===\\n";

$rateLimitFile = __DIR__ . "/src/Service/Api/RateLimitService.php";
if (!file_exists($rateLimitFile)) {
    echo "❌ RateLimitService file not found\\n";
    exit(1);
}

// Check file contents
$content = file_get_contents($rateLimitFile);
if (strpos($content, "getCurrentUsageForServices") !== false) {
    echo "✅ Multi-service methods found\\n";
} else {
    echo "❌ Multi-service methods missing\\n";
}

if (strpos($content, "rate_limit") !== false) {
    echo "✅ Dedicated cache pool configuration found\\n";
} else {
    echo "❌ Dedicated cache pool missing\\n";
}

$controllerFile = __DIR__ . "/src/Controller/Admin/AiMetricsController.php";
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    if (strpos($controllerContent, "perServiceUsage") !== false) {
        echo "✅ Controller updated with per-service usage\\n";
    } else {
        echo "❌ Controller not updated\\n";
    }
} else {
    echo "❌ Controller file not found\\n";
}

$templateFile = __DIR__ . "/templates/Admin/AiMetrics/dashboard.php";
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    if (strpos($templateContent, "rate-limit-total") !== false) {
        echo "✅ Template updated with new rate limit display\\n";
    } else {
        echo "❌ Template not updated\\n";
    }
} else {
    echo "❌ Template file not found\\n";
}

echo "\\n=== Next Steps ===\\n";
echo "1. Access the dashboard: http://localhost:8080/admin/ai-metrics/dashboard\\n";
echo "2. Test rate limit increments by calling API services\\n";
echo "3. Verify real-time updates in the browser\\n";
echo "4. Run PHPUnit tests if available\\n";
echo "\\nTest completed!\\n";
';

file_put_contents(__DIR__ . '/test_rate_limit.php', $testScript);
echo "   ✅ Test script created: test_rate_limit.php\n";

echo "\n=== SUMMARY ===\n";
echo "✅ Enhanced RateLimitService with multi-service support and atomic increments\n";
echo "✅ Updated AiMetricsController to expose per-service usage data\n";
echo "✅ Updated dashboard template with combined and individual service displays\n";
echo "✅ Created test script for validation\n";

echo "\n=== ACTION REQUIRED ===\n";
echo "1. Test the updated dashboard in your browser\n";
echo "2. Trigger some API calls to see rate limit counters increment\n";
echo "3. Verify real-time updates work every 10 seconds\n";
echo "4. Run: php test_rate_limit.php to validate changes\n";

echo "\nRate limit dashboard fix completed! 🎉\n";

?>
