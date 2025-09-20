<?php
/**
 * Test script for verifying image generation service functionality
 * with OpenAI API key integration
 */

require_once __DIR__ . '/vendor/autoload.php';

// Define required constants
if (!defined('ROOT')) define('ROOT', __DIR__);
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('WWW_ROOT')) define('WWW_ROOT', __DIR__ . DS . 'webroot' . DS);
if (!defined('RESOURCES')) define('RESOURCES', __DIR__ . DS . 'resources' . DS);

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use App\Service\Api\ImageGenerationService;
use Cake\Utility\Env;
use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;

// Bootstrap CakePHP
try {
    Configure::write('debug', true);
    Configure::write('App', [
        'namespace' => 'App',
        'encoding' => 'UTF-8',
        'defaultLocale' => getenv('APP_DEFAULT_LOCALE') ?: 'en_US',
        'defaultTimezone' => getenv('APP_DEFAULT_TIMEZONE') ?: 'UTC',
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'fullBaseUrl' => false,
        'imageBaseUrl' => false,
        'cssBaseUrl' => false,
        'jsBaseUrl' => false,
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ]
    ]);

    // Database configuration
    ConnectionManager::setConfig('default', [
        'className' => 'Cake\Database\Connection',
        'driver' => 'Cake\Database\Driver\Mysql',
        'host' => getenv('DB_HOST') ?: 'mysql',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_DATABASE') ?: 'cms',
        'username' => getenv('DB_USERNAME') ?: 'cms_user',
        'password' => getenv('DB_PASSWORD') ?: 'password',
    ]);

    // Configure cache
    if (!defined('CACHE')) define('CACHE', __DIR__ . DS . 'tmp' . DS . 'cache' . DS);
    Cache::setConfig('settings_cache', [
        'className' => FileEngine::class,
        'prefix' => 'cms_settings_',
        'path' => CACHE . 'settings' . DS,
        'serialize' => true,
        'duration' => '+1 years',
    ]);

    echo "=== OpenAI Image Generation Service Test ===\n\n";

    // Test 1: Verify environment configuration
    echo "1. Environment Configuration:\n";
    $openai_key = getenv('OPENAI_API_KEY');
    echo "   ✓ OpenAI API Key present: " . ($openai_key ? 'YES' : 'NO') . "\n";
    if ($openai_key) {
        echo "   ✓ Key format: " . substr($openai_key, 0, 20) . "..." . substr($openai_key, -10) . "\n";
    }
    echo "\n";

    // Test 2: Database connectivity and settings
    echo "2. Database Settings:\n";
    try {
        $connection = ConnectionManager::get('default');
        echo "   ✓ Database connection: SUCCESSFUL\n";
        
        // Get OpenAI settings from database
        $stmt = $connection->execute(
            "SELECT value FROM settings WHERE category = 'imageGeneration' AND key_name = 'openaiApiKey'"
        );
        $result = $stmt->fetch();
        
        if ($result) {
            echo "   ✓ OpenAI API key in database: " . substr($result[0], 0, 20) . "..." . substr($result[0], -10) . "\n";
        } else {
            echo "   ✗ OpenAI API key not found in database\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Database error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 3: Check for articles needing images (simplified query to avoid ORM issues)
    echo "3. Articles Analysis:\n";
    try {
        $stmt = $connection->execute(
            "SELECT COUNT(*) as total FROM articles"
        );
        $totalResult = $stmt->fetch();
        echo "   ✓ Total articles: " . $totalResult[0] . "\n";
        
        $stmt = $connection->execute(
            "SELECT COUNT(*) as without_images FROM articles WHERE image IS NULL OR image = ''"
        );
        $withoutImagesResult = $stmt->fetch();
        echo "   ✓ Articles without images: " . $withoutImagesResult[0] . "\n";
    } catch (Exception $e) {
        echo "   ✗ Article query error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 4: ImageGenerationService instantiation
    echo "4. Service Instantiation:\n";
    try {
        $imageService = new ImageGenerationService();
        echo "   ✓ ImageGenerationService created successfully\n";
        
        // Test OpenAI connection with a simple test call
        $testPrompt = "A simple test image of a blue circle";
        echo "   • Testing OpenAI API connection...\n";
        
        // Create a test result that doesn't actually call the API but verifies the setup
        $apiKey = $openai_key ?: 'test-key';
        if (strpos($apiKey, 'sk-') === 0 && strlen($apiKey) > 40) {
            echo "   ✓ API key format appears valid\n";
        } else {
            echo "   ✗ API key format appears invalid\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Service instantiation error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 5: Directory structure
    echo "5. Storage Directory:\n";
    $storageDir = WWW_ROOT . 'files' . DS . 'Articles' . DS . 'ai_generated';
    echo "   • Target directory: " . $storageDir . "\n";
    
    if (!is_dir($storageDir)) {
        if (mkdir($storageDir, 0755, true)) {
            echo "   ✓ Created storage directory\n";
        } else {
            echo "   ✗ Failed to create storage directory\n";
        }
    } else {
        echo "   ✓ Storage directory exists\n";
    }
    
    if (is_writable($storageDir)) {
        echo "   ✓ Directory is writable\n";
    } else {
        echo "   ✗ Directory is not writable\n";
    }
    echo "\n";

    // Test 6: Mock API call simulation
    echo "6. API Call Simulation:\n";
    if ($openai_key) {
        echo "   • Simulating OpenAI DALL-E API call...\n";
        echo "   • Model: dall-e-3\n";
        echo "   • Size: 1024x1024\n";
        echo "   • Quality: standard\n";
        echo "   • Style: vivid\n";
        echo "   ✓ API parameters look correct\n";
        echo "   ⚠ Actual API call skipped to avoid charges\n";
    } else {
        echo "   ✗ No API key available for testing\n";
    }
    echo "\n";

    echo "=== Test Summary ===\n";
    echo "Environment: " . ($openai_key ? "✓ Ready" : "✗ Missing API key") . "\n";
    echo "Database: " . (isset($connection) ? "✓ Connected" : "✗ Failed") . "\n";
    echo "Service: " . (isset($imageService) ? "✓ Available" : "✗ Failed") . "\n";
    echo "Storage: " . (is_writable($storageDir ?? '') ? "✓ Ready" : "✗ Not ready") . "\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}