<?php
/**
 * Fix Table Names in Smoke Tests
 * 
 * This script fixes the table name references in smoke tests to use proper
 * Table class names instead of lowercase inflections that don't match actual tables.
 */

$tableNameMapping = [
    "strtolower('CableCapabilities')" => "'Products'", // Uses products table
    "strtolower('HomepageFeeds')" => "'HomepageFeeds'",
    "strtolower('ImageGeneration')" => "'ImageGenerations'",
    "strtolower('ProductPageViews')" => "'ProductPageViews'",
    "strtolower('Cache')" => "'Cache'",
    "strtolower('Videos')" => "'Videos'",
    "strtolower('Reliability')" => "'ProductsReliability'",
    "strtolower('ProductFormFields')" => "'ProductFormFields'",
    "strtolower('Pages')" => "'Articles'", // Pages use articles table
];

$testDir = '/Volumes/1TB_DAVINCI/docker/willow/app/tests/TestCase/Controller/Admin';
$files = glob("$testDir/*ControllerTest.php");

echo "Fixing table names in smoke tests...\n\n";

$fixed = 0;
$errors = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $changed = false;
    
    foreach ($tableNameMapping as $old => $new) {
        if (strpos($content, $old) !== false) {
            $content = str_replace($old, $new, $content);
            $changed = true;
            echo "✓ Fixed table name in: " . basename($file) . "\n";
            echo "  Changed: $old\n";
            echo "  To: $new\n\n";
        }
    }
    
    if ($changed) {
        if (file_put_contents($file, $content)) {
            $fixed++;
        } else {
            echo "✗ Error writing to: " . basename($file) . "\n\n";
            $errors++;
        }
    }
}

echo "\n=================================\n";
echo "Summary:\n";
echo "=================================\n";
echo "Files fixed: $fixed\n";
echo "Errors: $errors\n";

if ($errors === 0) {
    echo "\n✅ All table names fixed successfully!\n";
    exit(0);
} else {
    echo "\n⚠️  Some errors occurred.\n";
    exit(1);
}
