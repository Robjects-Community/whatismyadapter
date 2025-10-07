<?php
declare(strict_types=1);

/**
 * Controller Analyzer
 * 
 * Scans all controllers in the WillowCMS application and generates
 * a JSON manifest with metadata for test generation.
 */

require __DIR__ . '/../../vendor/autoload.php';

use Cake\Utility\Inflector;

// Configuration
$appPath = dirname(__DIR__, 2);
$controllerPath = $appPath . '/src/Controller';
$outputPath = __DIR__ . '/controller_manifest.json';

echo "🔍 Analyzing WillowCMS Controllers\n";
echo "===================================\n\n";

/**
 * Recursively find all controller files
 *
 * @param string $directory Directory to scan
 * @return array List of controller file paths
 */
function findControllers(string $directory): array
{
    $controllers = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filename = $file->getFilename();
            // Skip traits and non-controller files
            if (str_ends_with($filename, 'Controller.php') && 
                !str_ends_with($filename, 'Trait.php')) {
                $controllers[] = $file->getPathname();
            }
        }
    }
    
    return $controllers;
}

/**
 * Get controller type based on path
 *
 * @param string $filePath Controller file path
 * @return string Controller type (admin, api, or root)
 */
function getControllerType(string $filePath): string
{
    if (str_contains($filePath, '/Admin/')) {
        return 'admin';
    } elseif (str_contains($filePath, '/Api/')) {
        return 'api';
    }
    return 'root';
}

/**
 * Get controller namespace from file path
 *
 * @param string $filePath Controller file path
 * @param string $appPath Application root path
 * @return string Controller namespace
 */
function getControllerNamespace(string $filePath, string $appPath): string
{
    $relativePath = str_replace($appPath . '/src/', '', dirname($filePath));
    $namespace = 'App\\' . str_replace('/', '\\', $relativePath);
    return $namespace;
}

/**
 * Extract public methods from controller file
 *
 * @param string $filePath Controller file path
 * @return array List of public method names
 */
function extractPublicMethods(string $filePath): array
{
    $content = file_get_contents($filePath);
    $methods = [];
    
    // Match public function declarations
    preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $method) {
            // Skip magic methods and inherited methods
            if (!str_starts_with($method, '__')) {
                $methods[] = $method;
            }
        }
    }
    
    return $methods;
}

/**
 * Find unauthenticated methods by looking for allowUnauthenticated calls
 *
 * @param string $filePath Controller file path
 * @return array List of unauthenticated method names
 */
function extractUnauthenticatedMethods(string $filePath): array
{
    $content = file_get_contents($filePath);
    $unauthenticated = [];
    
    // Look for allowUnauthenticated calls
    if (preg_match('/allowUnauthenticated\(\[(.*?)\]\)/', $content, $matches)) {
        $methodsString = $matches[1];
        // Extract method names from the array
        preg_match_all('/[\'"](\w+)[\'"]/', $methodsString, $methodMatches);
        if (!empty($methodMatches[1])) {
            $unauthenticated = $methodMatches[1];
        }
    }
    
    return $unauthenticated;
}

/**
 * Determine model name from controller
 *
 * @param string $controllerName Controller name (without Controller suffix)
 * @return string|null Model name or null
 */
function getModelName(string $controllerName): ?string
{
    // Skip special controllers that don't have models
    $noModelControllers = [
        'App', 'Error', 'Home', 'Health', 'Robots', 'Sitemap', 
        'Rss', 'LoginTest', 'Cache', 'AdminCrud'
    ];
    
    if (in_array($controllerName, $noModelControllers)) {
        return null;
    }
    
    // For most controllers, the model name matches the controller
    return $controllerName;
}

/**
 * Determine required fixtures for controller
 *
 * @param string|null $modelName Primary model name
 * @param string $type Controller type
 * @return array List of fixture names
 */
function getRequiredFixtures(?string $modelName, string $type): array
{
    $fixtures = [];
    
    // Admin and authenticated controllers need Users fixture
    if ($type === 'admin' || $type === 'api') {
        $fixtures[] = 'Users';
    }
    
    // Add primary model fixture if exists
    if ($modelName !== null) {
        $fixtures[] = $modelName;
    }
    
    return array_unique($fixtures);
}

/**
 * Get parent class from controller file
 *
 * @param string $filePath Controller file path
 * @return string Parent class name
 */
function getParentClass(string $filePath): string
{
    $content = file_get_contents($filePath);
    
    if (preg_match('/class\s+\w+\s+extends\s+(\w+)/', $content, $matches)) {
        return $matches[1];
    }
    
    return 'AppController';
}

// Main execution
$controllers = findControllers($controllerPath);
$manifest = [];
$count = 0;

foreach ($controllers as $filePath) {
    $filename = basename($filePath);
    $controllerName = str_replace('Controller.php', '', $filename);
    $type = getControllerType($filePath);
    $namespace = getControllerNamespace($filePath, $appPath);
    $publicMethods = extractPublicMethods($filePath);
    $unauthenticatedMethods = extractUnauthenticatedMethods($filePath);
    $parentClass = getParentClass($filePath);
    $modelName = getModelName($controllerName);
    $fixtures = getRequiredFixtures($modelName, $type);
    
    // Create manifest key
    $manifestKey = $type === 'root' ? $controllerName : $type . '/' . $controllerName;
    
    $manifest[$manifestKey] = [
        'namespace' => $namespace,
        'type' => $type,
        'file_path' => str_replace($appPath, '', $filePath),
        'extends' => $parentClass,
        'public_methods' => $publicMethods,
        'unauthenticated_methods' => $unauthenticatedMethods,
        'model' => $modelName,
        'requires_fixtures' => $fixtures,
    ];
    
    $count++;
    echo "✓ Analyzed: {$manifestKey}\n";
}

// Save manifest
file_put_contents($outputPath, json_encode($manifest, JSON_PRETTY_PRINT));

echo "\n===================================\n";
echo "✅ Analyzed {$count} controllers\n";
echo "📄 Manifest saved to: {$outputPath}\n\n";

// Display summary
$typeCount = [
    'root' => 0,
    'admin' => 0,
    'api' => 0,
];

foreach ($manifest as $data) {
    $typeCount[$data['type']]++;
}

echo "Summary:\n";
echo "  - Root controllers: {$typeCount['root']}\n";
echo "  - Admin controllers: {$typeCount['admin']}\n";
echo "  - API controllers: {$typeCount['api']}\n";
echo "  - Total: {$count}\n";
