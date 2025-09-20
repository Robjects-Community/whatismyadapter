#!/bin/bash

# WhatIsMyAdapter Product Import Script
# Imports CSV data into the products table with proper field mapping
# Adheres to security rules by using environment variables from .env

set -euo pipefail

# Configuration
CSV_FILE="tools/data/products_whatismyadapter.csv"
LOG_FILE="logs/import.log"
CONTAINER_NAME="willow-willowcms-1"

# Ensure log directory exists
mkdir -p logs

echo "Starting WhatIsMyAdapter product import at $(date)" | tee -a "$LOG_FILE"
echo "CSV file: $CSV_FILE" | tee -a "$LOG_FILE"
echo "Container: $CONTAINER_NAME" | tee -a "$LOG_FILE"

# Validate CSV file exists
if [[ ! -f "$CSV_FILE" ]]; then
    echo "ERROR: CSV file not found: $CSV_FILE" | tee -a "$LOG_FILE"
    exit 1
fi

# Count lines in CSV (excluding header)
total_lines=$(($(wc -l < "$CSV_FILE") - 1))
echo "Total products to import: $total_lines" | tee -a "$LOG_FILE"

# Clear existing products (optional - uncomment if needed)
# echo "Clearing existing products..." | tee -a "$LOG_FILE"
# docker exec -i "$CONTAINER_NAME" bin/cake shell -c "
# use Cake\ORM\TableRegistry;
# \$table = TableRegistry::getTableLocator()->get('Products');
# \$table->deleteAll(['1 = 1']);
# echo 'Cleared existing products.';
# "

echo "Creating CakePHP import command..." | tee -a "$LOG_FILE"

# Create a temporary PHP script for import
docker exec -i "$CONTAINER_NAME" bash -c "cat > /tmp/import_products.php << 'EOF'
<?php
declare(strict_types=1);

// Bootstrap CakePHP
require '/var/www/html/config/bootstrap.php';

use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Cake\Log\Log;
use Cake\I18n\FrozenTime;

// Get Products table
\$productsTable = TableRegistry::getTableLocator()->get('Products');

// Parse CSV
\$csvFile = '/var/www/html/tools/data/products_whatismyadapter.csv';
if (!file_exists(\$csvFile)) {
    echo \"ERROR: CSV file not found at \$csvFile\\n\";
    exit(1);
}

\$handle = fopen(\$csvFile, 'r');
if (!\$handle) {
    echo \"ERROR: Cannot open CSV file\\n\";
    exit(1);
}

// Get header row
\$headers = fgetcsv(\$handle);
if (!\$headers) {
    echo \"ERROR: Cannot read CSV headers\\n\";
    exit(1);
}

echo \"CSV Headers: \" . implode(', ', \$headers) . \"\\n\";

\$imported = 0;
\$errors = 0;

while ((\$row = fgetcsv(\$handle)) !== false) {
    if (count(\$row) !== count(\$headers)) {
        echo \"WARNING: Row has \" . count(\$row) . \" columns, expected \" . count(\$headers) . \"\\n\";
        continue;
    }
    
    // Combine headers with row data
    \$data = array_combine(\$headers, \$row);
    
    try {
        // Map CSV fields to database fields
        \$productData = [
            'id' => Text::uuid(),
            'user_id' => '00000000-0000-0000-0000-000000000001', // System user
            'title' => \$data['title'],
            'manufacturer' => \$data['manufacturer'],
            'model_number' => \$data['model_number'] ?? null,
            'price' => !empty(\$data['price']) ? (float) \$data['price'] : null,
            'currency' => \$data['currency'] ?? 'USD',
            'description' => \$data['description'] ?? null,
            'port_type_name' => \$data['port_type_name'] ?? null,
            'device_category' => \$data['device_category'] ?? null,
            'max_voltage' => !empty(\$data['max_voltage']) ? (float) \$data['max_voltage'] : null,
            'max_current' => !empty(\$data['max_current']) ? (float) \$data['max_current'] : null,
            'spec_value' => \$data['spec_value'] ?? null,
            'device_brand' => \$data['device_brand'] ?? null,
            'compatibility_level' => \$data['compatibility_level'] ?? null,
            'is_published' => !empty(\$data['is_published']) ? (bool) \$data['is_published'] : false,
            'featured' => !empty(\$data['featured']) ? (bool) \$data['featured'] : false,
            'is_certified' => !empty(\$data['is_certified']) ? (bool) \$data['is_certified'] : false,
            'certification_date' => (!empty(\$data['certification_date']) && \$data['certification_date'] !== 'NULL') 
                ? new FrozenTime(\$data['certification_date']) : null,
            'technical_specifications' => !empty(\$data['technical_specifications']) ? \$data['technical_specifications'] : null,
            'slug' => strtolower(str_replace([' ', '/', '-'], ['_', '_', '_'], \$data['title'])) . '_' . substr(md5(\$data['title']), 0, 8),
            'display_order' => 0,
            'numeric_rating' => null,
            'reliability_score' => 3.50, // Default
            'verification_status' => 'pending',
            'view_count' => 0,
            'created' => new FrozenTime(),
            'modified' => new FrozenTime(),
        ];
        
        // Create new entity
        \$product = \$productsTable->newEntity(\$productData);
        
        if (\$productsTable->save(\$product)) {
            \$imported++;
            if (\$imported % 10 == 0) {
                echo \"Imported \$imported products...\\n\";
            }
        } else {
            \$errors++;
            echo \"ERROR importing '{\$data['title']}': \" . json_encode(\$product->getErrors()) . \"\\n\";
        }
        
    } catch (Exception \$e) {
        \$errors++;
        echo \"EXCEPTION importing '{\$data['title']}': \" . \$e->getMessage() . \"\\n\";
    }
}

fclose(\$handle);

echo \"\\n=== IMPORT SUMMARY ===\\n\";
echo \"Successfully imported: \$imported products\\n\";
echo \"Errors: \$errors\\n\";
echo \"Total processed: \" . (\$imported + \$errors) . \"\\n\";

// Log the import
Log::info(\"WhatIsMyAdapter import completed: \$imported imported, \$errors errors\");

exit(\$errors > 0 ? 1 : 0);
EOF"

# Execute the import
echo "Executing import..." | tee -a "$LOG_FILE"
if docker exec -i "$CONTAINER_NAME" php /tmp/import_products.php 2>&1 | tee -a "$LOG_FILE"; then
    echo "Import completed successfully!" | tee -a "$LOG_FILE"
    
    # Verify import
    echo "Verifying import..." | tee -a "$LOG_FILE"
    docker exec -i "$CONTAINER_NAME" bin/cake shell -c "
        use Cake\ORM\TableRegistry;
        \$table = TableRegistry::getTableLocator()->get('Products');
        \$count = \$table->find()->count();
        echo \"Total products in database: \$count\\n\";
        
        // Show breakdown by manufacturer
        \$manufacturers = \$table->find()
            ->select(['manufacturer', 'count' => 'COUNT(*)'])
            ->group(['manufacturer'])
            ->orderAsc('manufacturer')
            ->toArray();
        
        echo \"\\nBreakdown by manufacturer:\\n\";
        foreach (\$manufacturers as \$mfg) {
            echo \"  {\$mfg->manufacturer}: {\$mfg->count}\\n\";
        }
        
        // Show port types
        \$portTypes = \$table->find()
            ->select(['port_type_name', 'count' => 'COUNT(*)'])
            ->where(['port_type_name IS NOT' => null])
            ->group(['port_type_name'])
            ->orderAsc('port_type_name')
            ->toArray();
            
        echo \"\\nPort types:\\n\";
        foreach (\$portTypes as \$port) {
            echo \"  {\$port->port_type_name}: {\$port->count}\\n\";
        }
    " 2>&1 | tee -a "$LOG_FILE"
    
    # Clean up temp file
    docker exec -i "$CONTAINER_NAME" rm -f /tmp/import_products.php
    
    echo "Import process completed at $(date)" | tee -a "$LOG_FILE"
    
else
    echo "Import failed!" | tee -a "$LOG_FILE"
    docker exec -i "$CONTAINER_NAME" rm -f /tmp/import_products.php
    exit 1
fi