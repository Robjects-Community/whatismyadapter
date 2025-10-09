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

// Create schema directory if it doesn't exist
$schemaDir = dirname(__DIR__) . "/tests/schema";
if (!is_dir($schemaDir)) {
    mkdir($schemaDir, 0755, true);
}

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
        $schemaFile = $schemaDir . "/{$tableName}.php";
        
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
        
        // Add primary key constraint
        $primaryKey = $schema->getPrimaryKey();
        if (!empty($primaryKey)) {
            $pkArray = "['" . implode("', '", $primaryKey) . "']";
            $content .= "    \$table->addConstraint('primary', [\n";
            $content .= "        'type' => 'primary',\n";
            $content .= "        'columns' => {$pkArray}\n";
            $content .= "    ]);\n\n";
        }
        
        // Add indexes
        $indexes = $schema->indexes();
        foreach ($indexes as $indexName) {
            $indexData = $schema->getIndex($indexName);
            $columns = "['" . implode("', '", $indexData['columns']) . "']";
            $options = [];
            
            // Only add type if it's unique or fulltext
            if (!empty($indexData['type']) && in_array($indexData['type'], ['unique', 'fulltext'])) {
                $options[] = "'type' => '{$indexData['type']}'";
            }
            
            $optionsStr = !empty($options) ? ', [' . implode(', ', $options) . ']' : '';
            $content .= "    \$table->addIndex('{$indexName}', {$columns}{$optionsStr});\n";
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
