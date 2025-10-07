#!/usr/bin/env php
<?php
/**
 * Fix Fixture Schemas for SQLite Compatibility
 *
 * This script adds explicit schema definitions to fixtures that are missing them,
 * ensuring compatibility with SQLite test database.
 *
 * Usage:
 *   php app/tools/test-generator/fix_fixture_schemas.php
 */

declare(strict_types=1);

// Schema definitions for problematic fixtures
$schemaDefinitions = [
    'ProductsFixture' => [
        'table' => 'products',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'user_id' => ['type' => 'string', 'length' => 36, 'null' => true],
            'article_id' => ['type' => 'string', 'length' => 36, 'null' => true],
            'parent_id' => ['type' => 'string', 'length' => 36, 'null' => true],
            'lft' => ['type' => 'integer', 'null' => true],
            'rght' => ['type' => 'integer', 'null' => true],
            'kind' => ['type' => 'string', 'length' => 255, 'null' => true],
            'title' => ['type' => 'string', 'length' => 255, 'null' => true],
            'slug' => ['type' => 'string', 'length' => 255, 'null' => true],
            'description' => ['type' => 'text', 'null' => true],
            'manufacturer' => ['type' => 'string', 'length' => 255, 'null' => true],
            'model_number' => ['type' => 'string', 'length' => 255, 'null' => true],
            'price' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => true],
            'currency' => ['type' => 'string', 'length' => 3, 'null' => true],
            'image' => ['type' => 'string', 'length' => 255, 'null' => true],
            'alt_text' => ['type' => 'string', 'length' => 255, 'null' => true],
            'capability_name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'capability_category' => ['type' => 'string', 'length' => 255, 'null' => true],
            'technical_specifications' => ['type' => 'text', 'null' => true],
            'testing_standard' => ['type' => 'string', 'length' => 255, 'null' => true],
            'certifying_organization' => ['type' => 'string', 'length' => 255, 'null' => true],
            'capability_value' => ['type' => 'string', 'length' => 255, 'null' => true],
            'numeric_rating' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'is_certified' => ['type' => 'boolean', 'null' => true],
            'certification_date' => ['type' => 'date', 'null' => true],
            'parent_category_name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'category_description' => ['type' => 'text', 'null' => true],
            'category_icon' => ['type' => 'string', 'length' => 255, 'null' => true],
            'display_order' => ['type' => 'integer', 'null' => true],
            'port_type_name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'endpoint_position' => ['type' => 'string', 'length' => 50, 'null' => true],
            'is_detachable' => ['type' => 'boolean', 'null' => true],
            'adapter_functionality' => ['type' => 'text', 'null' => true],
            'physical_spec_name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'spec_value' => ['type' => 'string', 'length' => 255, 'null' => true],
            'numeric_value' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => true],
            'device_category' => ['type' => 'string', 'length' => 255, 'null' => true],
            'device_brand' => ['type' => 'string', 'length' => 255, 'null' => true],
            'device_model' => ['type' => 'string', 'length' => 255, 'null' => true],
            'compatibility_level' => ['type' => 'string', 'length' => 50, 'null' => true],
            'compatibility_notes' => ['type' => 'text', 'null' => true],
            'performance_rating' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'verification_date' => ['type' => 'date', 'null' => true],
            'verified_by' => ['type' => 'string', 'length' => 255, 'null' => true],
            'user_reported_rating' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'spec_type' => ['type' => 'string', 'length' => 50, 'null' => true],
            'measurement_unit' => ['type' => 'string', 'length' => 50, 'null' => true],
            'spec_description' => ['type' => 'text', 'null' => true],
            'port_family' => ['type' => 'string', 'length' => 255, 'null' => true],
            'form_factor' => ['type' => 'string', 'length' => 255, 'null' => true],
            'connector_gender' => ['type' => 'string', 'length' => 20, 'null' => true],
            'pin_count' => ['type' => 'integer', 'null' => true],
            'max_voltage' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => true],
            'max_current' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => true],
            'data_pin_count' => ['type' => 'integer', 'null' => true],
            'power_pin_count' => ['type' => 'integer', 'null' => true],
            'ground_pin_count' => ['type' => 'integer', 'null' => true],
            'electrical_shielding' => ['type' => 'string', 'length' => 255, 'null' => true],
            'durability_cycles' => ['type' => 'integer', 'null' => true],
            'introduced_date' => ['type' => 'date', 'null' => true],
            'deprecated_date' => ['type' => 'date', 'null' => true],
            'physical_specs_summary' => ['type' => 'string', 'length' => 255, 'null' => true],
            'prototype_notes' => ['type' => 'text', 'null' => true],
            'needs_normalization' => ['type' => 'boolean', 'null' => true],
            'is_published' => ['type' => 'boolean', 'null' => true],
            'featured' => ['type' => 'boolean', 'null' => true],
            'verification_status' => ['type' => 'string', 'length' => 50, 'null' => true],
            'reliability_score' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'view_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
            'created_by' => ['type' => 'string', 'length' => 36, 'null' => true],
            'modified_by' => ['type' => 'string', 'length' => 36, 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'ProductsPurchaseLinksFixture' => [
        'table' => 'products_purchase_links',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'product_id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'store_url' => ['type' => 'string', 'length' => 255, 'null' => true],
            'link_type' => ['type' => 'string', 'length' => 50, 'null' => true],
            'retailer_name' => ['type' => 'string', 'length' => 255, 'null' => true],
            'listed_price' => ['type' => 'decimal', 'length' => 10, 'precision' => 2, 'null' => true],
            'price_currency' => ['type' => 'string', 'length' => 3, 'null' => true],
            'last_price_check' => ['type' => 'datetime', 'null' => true],
            'link_status' => ['type' => 'string', 'length' => 50, 'null' => true],
            'affiliate_link' => ['type' => 'boolean', 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
            'modified' => ['type' => 'datetime', 'null' => false],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'ProductsReliabilityLogsFixture' => [
        'table' => 'products_reliability_logs',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'model' => ['type' => 'string', 'length' => 255, 'null' => false],
            'foreign_key' => ['type' => 'string', 'length' => 36, 'null' => false],
            'from_total_score' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'to_total_score' => ['type' => 'decimal', 'length' => 5, 'precision' => 2, 'null' => true],
            'from_field_scores_json' => ['type' => 'text', 'null' => true],
            'to_field_scores_json' => ['type' => 'text', 'null' => true],
            'source' => ['type' => 'string', 'length' => 255, 'null' => true],
            'actor_user_id' => ['type' => 'string', 'length' => 36, 'null' => true],
            'actor_service' => ['type' => 'string', 'length' => 255, 'null' => true],
            'message' => ['type' => 'text', 'null' => true],
            'checksum_sha256' => ['type' => 'string', 'length' => 64, 'null' => true],
            'created' => ['type' => 'datetime', 'null' => false],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ],
    'ArticlesTranslationsFixture' => [
        'table' => 'articles_translations',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'locale' => ['type' => 'string', 'length' => 5, 'null' => false],
            'title' => ['type' => 'string', 'length' => 255, 'null' => true],
            'lede' => ['type' => 'string', 'length' => 255, 'null' => true],
            'body' => ['type' => 'text', 'null' => true],
            'summary' => ['type' => 'text', 'null' => true],
            'meta_title' => ['type' => 'text', 'null' => true],
            'meta_description' => ['type' => 'text', 'null' => true],
            'meta_keywords' => ['type' => 'text', 'null' => true],
            'facebook_description' => ['type' => 'text', 'null' => true],
            'linkedin_description' => ['type' => 'text', 'null' => true],
            'instagram_description' => ['type' => 'text', 'null' => true],
            'twitter_description' => ['type' => 'text', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
    ],
    'TagsTranslationsFixture' => [
        'table' => 'tags_translations',
        'columns' => [
            'id' => ['type' => 'string', 'length' => 36, 'null' => false],
            'locale' => ['type' => 'string', 'length' => 5, 'null' => false],
            'title' => ['type' => 'string', 'length' => 255, 'null' => true],
            'description' => ['type' => 'text', 'null' => true],
            'meta_title' => ['type' => 'text', 'null' => true],
            'meta_description' => ['type' => 'text', 'null' => true],
            'meta_keywords' => ['type' => 'text', 'null' => true],
            'facebook_description' => ['type' => 'text', 'null' => true],
            'linkedin_description' => ['type' => 'text', 'null' => true],
            'instagram_description' => ['type' => 'text', 'null' => true],
            'twitter_description' => ['type' => 'text', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id', 'locale']],
        ],
    ],
];

function generateSchemaProperty(array $definition): string
{
    $output = "    /**\n";
    $output .= "     * Table schema\n";
    $output .= "     *\n";
    $output .= "     * @var array\n";
    $output .= "     */\n";
    $output .= "    public array \$table = [\n";
    
    // Columns
    $output .= "        'columns' => [\n";
    foreach ($definition['columns'] as $column => $config) {
        $output .= "            '$column' => [\n";
        foreach ($config as $key => $value) {
            if (is_bool($value)) {
                $valueStr = $value ? 'true' : 'false';
            } elseif (is_int($value)) {
                $valueStr = (string)$value;
            } else {
                $valueStr = "'" . addslashes((string)$value) . "'";
            }
            $output .= "                '$key' => $valueStr,\n";
        }
        $output .= "            ],\n";
    }
    $output .= "        ],\n";
    
    // Constraints
    if (!empty($definition['constraints'])) {
        $output .= "        'constraints' => [\n";
        foreach ($definition['constraints'] as $constraintName => $constraintConfig) {
            $output .= "            '$constraintName' => [\n";
            foreach ($constraintConfig as $key => $value) {
                if ($key === 'columns' && is_array($value)) {
                    $output .= "                '$key' => ['" . implode("', '", $value) . "'],\n";
                } else {
                    $output .= "                '$key' => '" . addslashes((string)$value) . "',\n";
                }
            }
            $output .= "            ],\n";
        }
        $output .= "        ],\n";
    }
    
    $output .= "    ];\n\n";
    
    return $output;
}

function updateFixtureFile(string $fixturePath, string $schemaCode): bool
{
    if (!file_exists($fixturePath)) {
        echo "ERROR: Fixture file not found: $fixturePath\n";
        return false;
    }
    
    $content = file_get_contents($fixturePath);
    
    // Check if already has a table property
    if (preg_match('/public\s+(?:array\s+)?\$table\s*=/', $content)) {
        echo "INFO: Fixture already has a table property, skipping: $fixturePath\n";
        return true;
    }
    
    // Find where to insert the schema (after class declaration)
    if (preg_match('/(class\s+\w+\s+extends\s+TestFixture\s*\{)/', $content, $matches)) {
        $replacement = $matches[1] . "\n" . $schemaCode;
        $content = preg_replace('/(class\s+\w+\s+extends\s+TestFixture\s*\{)/', $replacement, $content, 1);
        
        if (file_put_contents($fixturePath, $content)) {
            echo "SUCCESS: Updated fixture: $fixturePath\n";
            return true;
        } else {
            echo "ERROR: Failed to write fixture file: $fixturePath\n";
            return false;
        }
    } else {
        echo "ERROR: Could not find class declaration in: $fixturePath\n";
        return false;
    }
}

// Main execution
echo "Fixing Fixture Schemas for SQLite Compatibility\n";
echo "================================================\n\n";

$fixtureDir = dirname(__DIR__, 2) . '/tests/Fixture';
$updated = 0;
$skipped = 0;
$errors = 0;

foreach ($schemaDefinitions as $fixtureName => $schemaDef) {
    $fixturePath = $fixtureDir . '/' . $fixtureName . '.php';
    $schemaCode = generateSchemaProperty($schemaDef);
    
    if (updateFixtureFile($fixturePath, $schemaCode)) {
        $updated++;
    } else {
        if (file_exists($fixturePath) && preg_match('/public\s+(?:array\s+)?\$table\s*=/', file_get_contents($fixturePath))) {
            $skipped++;
        } else {
            $errors++;
        }
    }
}

echo "\n";
echo "Summary:\n";
echo "--------\n";
echo "Updated: $updated\n";
echo "Skipped: $skipped\n";
echo "Errors: $errors\n";

exit($errors > 0 ? 1 : 0);
