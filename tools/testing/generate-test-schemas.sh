#!/bin/bash

################################################################################
# Generate Test Schema Files from Database
################################################################################
#
# Purpose: Generate schema definition files for all tables to support testing
#
# Features:
#   - Connects to database and extracts schema
#   - Generates PHP schema files for CakePHP 5.x tests
#   - Creates files in app/tests/schema/
#   - Properly formats columns, indexes, and constraints
#
# Usage:
#   ./tools/testing/generate-test-schemas.sh
#
################################################################################

set -euo pipefail

# --- Configuration ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
SCHEMA_DIR="${PROJECT_ROOT}/app/tests/schema"
DOCKER_SERVICE="willowcms"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}${BOLD}"
    echo "╔═══════════════════════════════════════════════════════════════╗"
    echo "║     Generate Test Schema Files from Database               ║"
    echo "╚═══════════════════════════════════════════════════════════════╝"
    echo -e "${RESET}"
}

print_error() {
    echo -e "${RED}${BOLD}ERROR:${RESET} ${RED}$*${RESET}" >&2
}

print_success() {
    echo -e "${GREEN}${BOLD}✓${RESET} ${GREEN}$*${RESET}"
}

print_info() {
    echo -e "${BLUE}${BOLD}ℹ${RESET}  ${BLUE}$*${RESET}"
}

print_step() {
    echo -e "${CYAN}${BOLD}➜${RESET} ${CYAN}$*${RESET}"
}

check_docker() {
    if ! docker compose ps --services --filter "status=running" | grep -q "^${DOCKER_SERVICE}$"; then
        print_error "Docker service '${DOCKER_SERVICE}' is not running"
        print_info "Run: docker compose up -d"
        exit 1
    fi
}

################################################################################
# Main Script
################################################################################

main() {
    print_header
    
    # Check Docker
    check_docker
    
    # Create schema directory
    print_step "Creating schema directory"
    mkdir -p "$SCHEMA_DIR"
    print_success "Schema directory ready: $SCHEMA_DIR"
    echo ""
    
    # Generate schema extraction PHP script
    print_step "Generating schema extraction script"
    
    cat > "${PROJECT_ROOT}/app/tmp/extract_schemas.php" << 'PHPEOF'
<?php
declare(strict_types=1);

// Bootstrap CakePHP
require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\TableSchemaInterface;

// Initialize app
$bootstrap = dirname(__DIR__) . '/config/bootstrap.php';
if (file_exists($bootstrap)) {
    require $bootstrap;
}

// Get connection
$connection = ConnectionManager::get('default');
$schemaCollection = $connection->getSchemaCollection();

// Get all tables
$tables = $schemaCollection->listTables();

echo "Found " . count($tables) . " tables\n\n";

foreach ($tables as $tableName) {
    // Skip migration tables
    if (in_array($tableName, ['phinxlog', 'queue_processes', 'queue_failed_jobs'])) {
        echo "Skipping: $tableName\n";
        continue;
    }
    
    echo "Extracting: $tableName\n";
    
    try {
        $schema = $schemaCollection->describe($tableName);
        
        // Generate PHP schema file
        $schemaFile = dirname(__DIR__) . "/tests/schema/{$tableName}.php";
        
        $content = "<?php\ndeclare(strict_types=1);\n\n";
        $content .= "use Cake\\Database\\Schema\\TableSchema;\n\n";
        $content .= "/**\n";
        $content .= " * Schema for {$tableName} table\n";
        $content .= " * Auto-generated from database\n";
        $content .= " */\n";
        $content .= "return function (TableSchema \$table) {\n";
        
        // Add columns
        foreach ($schema->columns() as $column) {
            $columnSchema = $schema->getColumn($column);
            $content .= "    \$table->addColumn('{$column}', '{$columnSchema['type']}', [\n";
            
            if (isset($columnSchema['length'])) {
                $content .= "        'length' => {$columnSchema['length']},\n";
            }
            if (isset($columnSchema['precision'])) {
                $content .= "        'precision' => {$columnSchema['precision']},\n";
            }
            if (isset($columnSchema['null'])) {
                $content .= "        'null' => " . ($columnSchema['null'] ? 'true' : 'false') . ",\n";
            }
            if (isset($columnSchema['default']) && $columnSchema['default'] !== null) {
                $default = var_export($columnSchema['default'], true);
                $content .= "        'default' => {$default},\n";
            }
            
            $content .= "    ]);\n";
        }
        
        $content .= "\n";
        
        // Add primary key
        $primaryKey = $schema->getPrimaryKey();
        if (!empty($primaryKey)) {
            $pkArray = "['" . implode("', '", $primaryKey) . "']";
            $content .= "    \$table->addPrimaryKey({$pkArray});\n\n";
        }
        
        // Add indexes
        $indexes = $schema->indexes();
        foreach ($indexes as $indexName) {
            $indexData = $schema->getIndex($indexName);
            $columns = "['" . implode("', '", $indexData['columns']) . "']";
            $options = [];
            
            if (!empty($indexData['type'])) {
                $options[] = "'type' => '{$indexData['type']}'";
            }
            
            $optionsStr = !empty($options) ? ', [' . implode(', ', $options) . ']' : '';
            $content .= "    \$table->addIndex({$columns}{$optionsStr});\n";
        }
        
        // Add constraints
        $constraints = $schema->constraints();
        foreach ($constraints as $constraintName) {
            if ($constraintName === 'primary') continue; // Skip primary, already added
            
            $constraintData = $schema->getConstraint($constraintName);
            $type = $constraintData['type'] ?? '';
            
            if ($type === 'unique') {
                $columns = "['" . implode("', '", $constraintData['columns']) . "']";
                $content .= "    \$table->addConstraint('{$constraintName}', ['type' => 'unique', 'columns' => {$columns}]);\n";
            }
        }
        
        $content .= "\n    return \$table;\n";
        $content .= "};\n";
        
        file_put_contents($schemaFile, $content);
        echo "  ✓ Created: {$schemaFile}\n";
        
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\nSchema extraction complete!\n";
PHPEOF
    
    print_success "Schema extraction script created"
    echo ""
    
    # Run the extraction script
    print_step "Extracting schemas from database"
    docker compose exec -T "$DOCKER_SERVICE" php tmp/extract_schemas.php
    
    echo ""
    print_success "Schema files generated in: $SCHEMA_DIR"
    
    # Count generated files
    SCHEMA_COUNT=$(ls -1 "$SCHEMA_DIR"/*.php 2>/dev/null | wc -l | tr -d ' ')
    print_info "Total schema files: $SCHEMA_COUNT"
    
    # Clean up
    rm -f "${PROJECT_ROOT}/app/tmp/extract_schemas.php"
    
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${RESET}"
    print_info "Next Steps:"
    echo ""
    echo "1. Review generated schema files:"
    echo "   ${CYAN}ls -la app/tests/schema/${RESET}"
    echo ""
    echo "2. Update tests/bootstrap.php to load schemas"
    echo ""
    echo "3. Run tests to verify:"
    echo "   ${CYAN}docker compose exec willowcms vendor/bin/phpunit tests/TestCase/Model/Table/UsersTableTest.php${RESET}"
    echo ""
    echo -e "${BLUE}════════════════════════════════════════════════════════════════${RESET}"
}

# Run main function
main "$@"
