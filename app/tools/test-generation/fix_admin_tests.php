<?php
declare(strict_types=1);

/**
 * Fix Admin Controller Tests
 * 
 * This script automatically fixes admin controller tests by:
 * 1. Adding proper IDs to view/edit/delete tests
 * 2. Changing HTTP methods from GET to POST/DELETE where needed
 * 3. Adding enableCsrf() for POST methods
 * 4. Using fixture IDs from the fixtures
 * 
 * Usage:
 *   php fix_admin_tests.php
 */

// Define methods that require POST
const POST_METHODS = ['delete', 'bulkAction', 'updateTree'];

// Define methods that require IDs as URL parameters
const ID_REQUIRED_METHODS = ['view', 'edit', 'delete'];

// Common fixture ID per model (from UsersFixture and ArticlesFixture)
const FIXTURE_IDS = [
    'Articles' => '12bc5d51-9b2e-42be-9ac8-e0837ab885e1',
    'Users' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
    'Products' => '6f91c7c1-1d1f-42e1-91e2-1d1f42e191e2', // From ProductsFixture
    'Settings' => '1',
    'Tags' => '1',
    'EmailTemplates' => '1',
    'Images' => '1',
    'ImageGalleries' => '1',
];

$testDir = __DIR__ . '/../../tests/TestCase/Controller/Admin';

if (!is_dir($testDir)) {
    echo "Error: Admin test directory not found: $testDir\n";
    exit(1);
}

$files = glob($testDir . '/*ControllerTest.php');
$fixedCount = 0;
$totalFiles = count($files);

echo "Found {$totalFiles} admin controller test files to fix...\n\n";

foreach ($files as $file) {
    $filename = basename($file);
    echo "Processing: $filename\n";
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Extract controller name and model name
    preg_match('/class (\w+)ControllerTest/', $content, $matches);
    $testClassName = $matches[1] ?? '';
    $modelName = str_replace('Controller', '', $testClassName);
    
    // Get fixture ID for this model
    $fixtureId = FIXTURE_IDS[$modelName] ?? '1';
    
    echo "  Model: $modelName, Fixture ID: $fixtureId\n";
    
    // Fix each test method
    $fixed = false;
    
    // Pattern 1: Fix view/edit/delete methods that need IDs
    foreach (ID_REQUIRED_METHODS as $method) {
        $camelMethod = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $method))));
        
        // Fix AsAdmin version - Add ID to URL
        $pattern = "/(public function test{$camelMethod}AsAdmin\(\): void\s*\{\s*\\\$this->mockAdminUser\(\);\s*\\\$this->(get|post|delete)\('\/admin\/\w+\/$method)'\)/i";
        $replacement = "$1/$fixtureId')";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            echo "    ✓ Fixed test{$camelMethod}AsAdmin - added ID\n";
            $fixed = true;
        }
        
        // Fix RequiresAdmin version - Add ID to URL  
        $pattern = "/(public function test{$camelMethod}RequiresAdmin\(\): void\s*\{\s*\\\$this->mockUnauthenticatedRequest\(\);\s*\\\$this->(get|post|delete)\('\/admin\/\w+\/$method)'\)/i";
        $replacement = "$1/$fixtureId')";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            echo "    ✓ Fixed test{$camelMethod}RequiresAdmin - added ID\n";
            $fixed = true;
        }
    }
    
    // Pattern 2: Fix POST methods - change GET to POST and add enableCsrf()
    foreach (POST_METHODS as $method) {
        $camelMethod = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $method))));
        $kebabMethod = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $method));
        
        // Fix AsAdmin version - Change to POST
        // Extract URL from existing test to preserve correct pluralization
        $urlPattern = "/\\/admin\/(\w+)\/$kebabMethod/";
        if (preg_match($urlPattern, $content, $urlMatches)) {
            $controllerPath = $urlMatches[1];
            $pattern = "/(public function test{$camelMethod}AsAdmin\(\): void\s*\{\s*\\\$this->mockAdminUser\(\);)\s*\\\$this->get\('\/admin\/$controllerPath\/$kebabMethod/i";
            $replacement = "$1\n        \$this->enableCsrf();\n        \$this->post('/admin/$controllerPath/$kebabMethod";
        
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
                echo "    ✓ Fixed test{$camelMethod}AsAdmin - changed to POST with CSRF\n";
                $fixed = true;
            }
        
            // Fix RequiresAdmin version - Change to POST
            $pattern = "/(public function test{$camelMethod}RequiresAdmin\(\): void\s*\{\s*\\\$this->mockUnauthenticatedRequest\(\);)\s*\\\$this->get\('\/admin\/\w+\/$kebabMethod/i";
            $replacement = "$1\n        \$this->post('/admin/" . strtolower($modelName) . "s/$kebabMethod";
        
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
                echo "    ✓ Fixed test{$camelMethod}RequiresAdmin - changed to POST\n";
                $fixed = true;
            }
        }
    }
    
    // Pattern 3: Special case - delete method should add ID if using POST
    if (in_array('delete', POST_METHODS)) {
        $pattern = "/(public function testDeleteAsAdmin\(\): void\s*\{\s*\\\$this->mockAdminUser\(\);\s*\\\$this->enableCsrf\(\);\s*\\\$this->post\('\/admin\/\w+\/delete)'\)/";
        $replacement = "$1/$fixtureId')";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            echo "    ✓ Fixed testDeleteAsAdmin - added ID to POST\n";
            $fixed = true;
        }
        
        $pattern = "/(public function testDeleteRequiresAdmin\(\): void\s*\{\s*\\\$this->mockUnauthenticatedRequest\(\);\s*\\\$this->post\('\/admin\/\w+\/delete)'\)/";
        $replacement = "$1/$fixtureId')";
        
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            echo "    ✓ Fixed testDeleteRequiresAdmin - added ID to POST\n";
            $fixed = true;
        }
    }
    
    if ($fixed && $content !== $original) {
        file_put_contents($file, $content);
        $fixedCount++;
        echo "  ✅ Saved changes to $filename\n\n";
    } else {
        echo "  ⏭️  No changes needed for $filename\n\n";
    }
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "SUMMARY:\n";
echo "  Total files processed: $totalFiles\n";
echo "  Files fixed: $fixedCount\n";
echo "  Files unchanged: " . ($totalFiles - $fixedCount) . "\n";
echo str_repeat('=', 70) . "\n";

echo "\nNext steps:\n";
echo "1. Run tests: docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin\n";
echo "2. Review any remaining failures\n";
echo "3. Update fixture IDs in this script if needed\n\n";
