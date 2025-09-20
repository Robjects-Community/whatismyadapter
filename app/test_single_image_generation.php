<?php
/**
 * Direct test for generating one image using the OpenAI API
 */

require_once __DIR__ . '/vendor/autoload.php';

// Define required constants
if (!defined('ROOT')) define('ROOT', __DIR__);
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('WWW_ROOT')) define('WWW_ROOT', __DIR__ . DS . 'webroot' . DS);
if (!defined('RESOURCES')) define('RESOURCES', __DIR__ . DS . 'resources' . DS);
if (!defined('CACHE')) define('CACHE', __DIR__ . DS . 'tmp' . DS . 'cache' . DS);

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use App\Service\Api\ImageGenerationService;
use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;

try {
    // Bootstrap CakePHP configuration
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
    Cache::setConfig('settings_cache', [
        'className' => FileEngine::class,
        'prefix' => 'cms_settings_',
        'path' => CACHE . 'settings' . DS,
        'serialize' => true,
        'duration' => '+1 years',
    ]);
    
    Cache::setConfig('rate_limit', [
        'className' => FileEngine::class,
        'prefix' => 'cms_rate_limit_',
        'path' => CACHE . 'ratelimit' . DS,
        'serialize' => true,
        'duration' => '+5 minutes',
    ]);

    echo "=== Single Article Image Generation Test ===\n\n";

    $connection = ConnectionManager::get('default');
    
    // Get the first article without an image
    $stmt = $connection->execute(
        "SELECT id, title, body FROM articles WHERE (image IS NULL OR image = '') LIMIT 1"
    );
    $article = $stmt->fetch('assoc');
    
    if (!$article) {
        echo "No articles found that need images.\n";
        exit(1);
    }
    
    echo "Selected Article:\n";
    echo "ID: " . $article['id'] . "\n";
    echo "Title: " . $article['title'] . "\n";
    echo "Content: " . substr($article['body'], 0, 100) . "...\n\n";
    
    // Initialize the image generation service
    $imageService = new ImageGenerationService();
    
    // Generate a prompt based on the article content
    $prompt = "Professional illustration based on: " . $article['title'];
    if (strlen($prompt) > 100) {
        $prompt = substr($prompt, 0, 100) . "...";
    }
    
    echo "Image Prompt: " . $prompt . "\n\n";
    echo "Calling OpenAI DALL-E API...\n";
    
    // Attempt to generate the image
    $result = $imageService->generateArticleImage($article['title'], $article['body']);
    
    if ($result && isset($result['success']) && $result['success']) {
        echo "✓ Image generated successfully!\n";
        echo "Image URL: " . $result['url'] . "\n";
        
        // Generate a local filename based on article ID
        $filename = 'article_' . $article['id'] . '_' . time() . '.png';
        $localPath = 'files/Articles/ai_generated/' . $filename;
        $fullPath = WWW_ROOT . $localPath;
        
        echo "Downloading image to: " . $localPath . "\n";
        
        // Download the image
        if ($imageService->downloadImage($result['url'], $fullPath)) {
            echo "✓ Image downloaded successfully\n";
            
            // Update the article with the new image path
            $updateStmt = $connection->execute(
                "UPDATE articles SET image = ?, modified = NOW() WHERE id = ?",
                [$localPath, $article['id']]
            );
            
            echo "✓ Article updated with image path: " . $localPath . "\n";
            echo "✓ Full file path: " . $fullPath . "\n";
            
            // Check file size
            if (file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                echo "✓ Downloaded file size: " . number_format($fileSize / 1024, 2) . " KB\n";
            }
        } else {
            echo "✗ Failed to download image\n";
        }
        
    } else {
        echo "✗ Image generation failed\n";
        if (isset($result['error'])) {
            echo "Error: " . $result['error'] . "\n";
        }
        if (isset($result['details'])) {
            echo "Details: " . $result['details'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}