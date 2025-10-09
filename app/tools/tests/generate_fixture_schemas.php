#!/usr/bin/env php
<?php
/**
 * Generate SQLite-compatible schema definitions for fixtures
 * 
 * This script reads the actual database schema from MySQL and generates
 * SQLite-compatible fixture schema definitions.
 */

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\TableSchemaInterface;

// Bootstrap the application
$bootstrap = dirname(__DIR__, 2) . '/app/config/bootstrap.php';
if (!file_exists($bootstrap)) {
    echo "Error: Cannot find bootstrap.php\n";
    exit(1);
}
require $bootstrap;

// Get database connection
try {
    $connection = ConnectionManager::get('default');
} catch (\Exception $e) {
    echo "Error connecting to database: " . $e->getMessage() . "\n";
    exit(1);
}

$fixturesDir = dirname(__DIR__, 2) . '/app/tests/Fixture';
$fixtures = glob($fixturesDir . '/*Fixture.php');

echo "Found " . count($fixtures) . " fixture files\n\n";

foreach ($fixtures as $fixtureFile) {
    $className = basename($fixtureFile, '.php');
    $tableName = \Cake\Utility\Inflector::tableize(str_replace('Fixture', '', $className));
    
    // Load fixture content
    $content = file_get_contents($fixtureFile);
    
    // Skip if it already has a schema definition
    if (preg_match('/public\s+\$fields\s*=/', $content)) {
        echo "⏭️  Skipping $className (already has schema)\n";
        continue;
    }
    
    // Try to get table schema from database
    try {
        $schemaCollection = $connection->getSchemaCollection();
        $tableSchema = $schemaCollection->describe($tableName);
        
        // Generate schema array
        $schema = generateSchemaArray($tableSchema);
        
        // Insert schema into fixture
        $newContent = insertSchemaIntoFixture($content, $schema);
        
        // Write back to file
        file_put_contents($fixtureFile, $newContent);
        
        echo "✅ Updated $className (table: $tableName)\n";
        
    } catch (\Exception $e) {
        echo "⚠️  Could not process $className: " . $e->getMessage() . "\n";
    }
}

echo "\n✨ Done!\n";

/**
 * Generate a schema array from a TableSchema object
 */
function generateSchemaArray(TableSchemaInterface $tableSchema): string
{
    $columns = [];
    
    foreach ($tableSchema->columns() as $columnName) {
        $column = $tableSchema->getColumn($columnName);
        $columnDef = [
            'type' => $column['type'],
        ];
        
        // Add length for string types
        if (in_array($column['type'], ['string', 'char', 'binary']) && isset($column['length'])) {
            $columnDef['length'] = $column['length'];
        }
        
        // Add precision and scale for decimal types
        if ($column['type'] === 'decimal' && isset($column['precision'])) {
            $columnDef['precision'] = $column['precision'];
            if (isset($column['scale'])) {
                $columnDef['scale'] = $column['scale'];
            }
        }
        
        // Add null constraint
        if (isset($column['null'])) {
            $columnDef['null'] = $column['null'];
        }
        
        // Add default value
        if (isset($column['default']) && $column['default'] !== null) {
            $columnDef['default'] = $column['default'];
        }
        
        // Add comment
        if (!empty($column['comment'])) {
            $columnDef['comment'] = $column['comment'];
        }
        
        $columns[$columnName] = $columnDef;
    }
    
    // Build constraints
    $constraints = [];
    
    // Primary key
    $primaryKey = $tableSchema->getPrimaryKey();
    if (!empty($primaryKey)) {
        $constraints['primary'] = [
            'type' => 'primary',
            'columns' => $primaryKey,
        ];
    }
    
    // Foreign keys
    foreach ($tableSchema->constraints() as $constraintName) {
        $constraint = $tableSchema->getConstraint($constraintName);
        if ($constraint['type'] === 'foreign') {
            $constraints[$constraintName] = $constraint;
        }
    }
    
    // Unique constraints
    foreach ($tableSchema->constraints() as $constraintName) {
        $constraint = $tableSchema->getConstraint($constraintName);
        if ($constraint['type'] === 'unique') {
            $constraints[$constraintName] = $constraint;
        }
    }
    
    // Indexes
    $indexes = [];
    foreach ($tableSchema->indexes() as $indexName) {
        $index = $tableSchema->getIndex($indexName);
        $indexes[$indexName] = $index;
    }
    
    // Format as PHP array
    $schema = "[\n";
    
    // Columns
    foreach ($columns as $colName => $colDef) {
        $schema .= "        '$colName' => " . arrayToString($colDef, 12) . ",\n";
    }
    
    // Constraints
    if (!empty($constraints)) {
        $schema .= "        '_constraints' => [\n";
        foreach ($constraints as $constName => $constDef) {
            $schema .= "            '$constName' => " . arrayToString($constDef, 16) . ",\n";
        }
        $schema .= "        ],\n";
    }
    
    // Indexes
    if (!empty($indexes)) {
        $schema .= "        '_indexes' => [\n";
        foreach ($indexes as $idxName => $idxDef) {
            $schema .= "            '$idxName' => " . arrayToString($idxDef, 16) . ",\n";
        }
        $schema .= "        ],\n";
    }
    
    $schema .= "    ]";
    
    return $schema;
}

/**
 * Convert array to formatted string
 */
function arrayToString(array $array, int $indent = 0): string
{
    $spaces = str_repeat(' ', $indent);
    $result = "[\n";
    
    foreach ($array as $key => $value) {
        $result .= $spaces . "    ";
        
        if (is_string($key)) {
            $result .= "'$key' => ";
        }
        
        if (is_array($value)) {
            $result .= arrayToString($value, $indent + 4);
        } elseif (is_string($value)) {
            $result .= "'" . addslashes($value) . "'";
        } elseif (is_bool($value)) {
            $result .= $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            $result .= 'null';
        } else {
            $result .= $value;
        }
        
        $result .= ",\n";
    }
    
    $result .= $spaces . "]";
    
    return $result;
}

/**
 * Insert schema definition into fixture content
 */
function insertSchemaIntoFixture(string $content, string $schema): string
{
    // Find the position after the class declaration
    $classPos = strpos($content, 'class ');
    $bracePos = strpos($content, '{', $classPos);
    
    if ($bracePos === false) {
        throw new \Exception("Could not find class opening brace");
    }
    
    // Insert schema after opening brace
    $schemaDeclaration = "\n    /**\n     * Fields\n     *\n     * @var array\n     */\n    public \$fields = " . $schema . ";\n";
    
    $newContent = substr($content, 0, $bracePos + 1) . $schemaDeclaration . substr($content, $bracePos + 1);
    
    return $newContent;
}
