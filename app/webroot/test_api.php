<?php
/**
 * Simple test API for rate limiting functionality
 */

// Set content type for JSON responses
if ($_GET['action'] !== 'get_metrics' || isset($_GET['format'])) {
    header('Content-Type: application/json');
}

// Include autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Basic cache configuration
use Cake\Cache\Cache;

// Set up file cache for rate limiting
if (!Cache::configured('rate_limit')) {
    $cacheDir = dirname(__DIR__) . '/tmp/cache/rate_limit/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    Cache::setConfig('rate_limit', [
        'className' => 'File',
        'duration' => '+1 hour',
        'prefix' => 'rl_',
        'path' => $cacheDir,
        'serialize' => true,
    ]);
}

// Simple stub settings class for testing
class StubSettings {
    public static function read(string $key, mixed $default = null): mixed {
        $values = [
            'AI.enableMetrics' => true,
            'AI.hourlyLimit' => 10,  // Lower limit for testing
            'AI.dailyCostLimit' => 50.00
        ];
        return $values[$key] ?? $default;
    }
}

// Initialize RateLimitService with stub settings
require_once dirname(__DIR__) . '/src/Service/Api/RateLimitService.php';
$rateLimitService = new \App\Service\Api\RateLimitService(StubSettings::class);

// Handle API actions
$action = $_GET['action'] ?? 'get_metrics';

switch ($action) {
    case 'get_metrics':
        $services = ['google', 'anthropic'];
        $serviceUsage = $rateLimitService->getCurrentUsageForServices($services);
        $combinedUsage = $rateLimitService->getCombinedUsage($services);
        
        echo json_encode([
            'success' => true,
            'combined' => $combinedUsage,
            'services' => $serviceUsage,
            'timestamp' => time()
        ]);
        break;
        
    case 'simulate_call':
        $service = $_GET['service'] ?? 'anthropic';
        $allowed = $rateLimitService->enforceLimit($service);
        
        if ($allowed) {
            echo json_encode([
                'success' => true,
                'message' => ucfirst($service) . " API call simulated successfully!"
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => ucfirst($service) . " API call blocked - rate limit exceeded!"
            ]);
        }
        break;
        
    case 'reset':
        $rateLimitService->resetUsage('google');
        $rateLimitService->resetUsage('anthropic');
        
        echo json_encode([
            'success' => true,
            'message' => 'Rate limit counters reset successfully!'
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Unknown action: ' . $action
        ]);
        break;
}
?>
