# Datasource Usage Examples

This document shows how to use the different database connections in your CakePHP application code.

## Available Datasources

Your application now has four separate datasource connections:

1. **`default`** - Local MySQL (for development)
2. **`test`** - Local MySQL test database  
3. **`digitalocean`** - DigitalOcean production database with SSL
4. **`digitalocean_test`** - DigitalOcean test database with SSL

## Basic Usage

### Using Default Connection (Local MySQL)

```php
use Cake\Datasource\ConnectionManager;

// Get default connection
$connection = ConnectionManager::get('default');

// Execute query
$results = $connection->execute('SELECT * FROM articles LIMIT 5')->fetchAll();

// Using Table classes (automatically uses 'default' connection)
$articlesTable = $this->getTableLocator()->get('Articles');
$articles = $articlesTable->find()->limit(5)->toArray();
```

### Using DigitalOcean Connection

```php
use Cake\Datasource\ConnectionManager;

// Get DigitalOcean connection
$connection = ConnectionManager::get('digitalocean');

// Execute query with SSL connection
$results = $connection->execute('SELECT * FROM articles LIMIT 5')->fetchAll();

// Using Table classes with specific connection
$articlesTable = $this->getTableLocator()->get('Articles');
$articlesTable->setConnection($connection);
$articles = $articlesTable->find()->limit(5)->toArray();
```

## Advanced Usage

### Switching Between Local and Production Data

```php
<?php
// In your Controller or Service

use Cake\Datasource\ConnectionManager;

class DataAnalysisController extends AppController
{
    public function compareData()
    {
        // Get data from local development database
        $localConnection = ConnectionManager::get('default');
        $localArticles = $localConnection
            ->execute('SELECT COUNT(*) as count FROM articles')
            ->fetch();

        // Get data from DigitalOcean production database
        $prodConnection = ConnectionManager::get('digitalocean');
        $prodArticles = $prodConnection
            ->execute('SELECT COUNT(*) as count FROM articles')
            ->fetch();

        $this->set(compact('localArticles', 'prodArticles'));
    }
}
```

### Using Different Connections in Table Classes

```php
<?php
// src/Model/Table/ArticlesTable.php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Datasource\ConnectionManager;

class ArticlesTable extends Table
{
    public function getProductionArticles()
    {
        // Use DigitalOcean connection for this specific query
        $prodConnection = ConnectionManager::get('digitalocean');
        $this->setConnection($prodConnection);
        
        return $this->find()->where(['published' => true]);
    }
    
    public function getLocalArticles()
    {
        // Switch back to local connection
        $localConnection = ConnectionManager::get('default');
        $this->setConnection($localConnection);
        
        return $this->find();
    }
}
```

### Environment-Based Connection Selection

```php
<?php
// src/Service/DatabaseService.php

namespace App\Service;

use Cake\Datasource\ConnectionManager;

class DatabaseService
{
    public function getConnection($useProduction = false)
    {
        if ($useProduction) {
            return ConnectionManager::get('digitalocean');
        }
        
        return ConnectionManager::get('default');
    }
    
    public function getArticles($useProduction = false)
    {
        $connection = $this->getConnection($useProduction);
        
        return $connection
            ->execute('SELECT * FROM articles ORDER BY created DESC LIMIT 10')
            ->fetchAll();
    }
}
```

### Testing with Different Databases

```php
<?php
// tests/TestCase/Model/Table/ArticlesTableTest.php

namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Cake\Datasource\ConnectionManager;

class ArticlesTableTest extends TestCase
{
    public function testLocalConnection()
    {
        // Test uses local 'test' connection automatically
        $articlesTable = $this->getTableLocator()->get('Articles');
        $article = $articlesTable->newEntity(['title' => 'Test Article']);
        $this->assertTrue($articlesTable->save($article));
    }
    
    public function testDigitalOceanTestConnection()
    {
        // Test using DigitalOcean test database
        $connection = ConnectionManager::get('digitalocean_test');
        $result = $connection->execute('SELECT 1 as test')->fetch();
        $this->assertEquals(1, $result['test']);
    }
}
```

## Configuration in Commands/Shell Scripts

```php
<?php
// src/Command/DataMigrationCommand.php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;

class DataMigrationCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $useProduction = $args->getOption('production');
        
        if ($useProduction) {
            $io->out('Using DigitalOcean production database');
            $connection = ConnectionManager::get('digitalocean');
        } else {
            $io->out('Using local development database');
            $connection = ConnectionManager::get('default');
        }
        
        // Perform migration logic
        $results = $connection->execute('SELECT COUNT(*) as count FROM articles')->fetch();
        $io->out("Found {$results['count']} articles");
        
        return static::CODE_SUCCESS;
    }
}
```

Run the command:
```bash
# Use local database
bin/cake data_migration

# Use DigitalOcean database
bin/cake data_migration --production
```

## Connection Testing Examples

### Health Check Service

```php
<?php
// src/Service/HealthCheckService.php

namespace App\Service;

use Cake\Datasource\ConnectionManager;
use Exception;

class HealthCheckService
{
    public function checkAllConnections()
    {
        $connections = ['default', 'test', 'digitalocean', 'digitalocean_test'];
        $results = [];
        
        foreach ($connections as $name) {
            try {
                $connection = ConnectionManager::get($name);
                $result = $connection->execute('SELECT 1 as test')->fetch();
                $results[$name] = [
                    'status' => 'OK',
                    'response_time' => microtime(true) - microtime(true)
                ];
            } catch (Exception $e) {
                $results[$name] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}
```

### Controller Endpoint

```php
<?php
// src/Controller/HealthController.php

class HealthController extends AppController
{
    public function database()
    {
        $healthCheck = new HealthCheckService();
        $results = $healthCheck->checkAllConnections();
        
        $this->viewBuilder()->setOption('serialize', ['results']);
        $this->set('results', $results);
    }
}
```

## Best Practices

### 1. Connection Management

```php
// Good: Store connection references
$prodConnection = ConnectionManager::get('digitalocean');
$localConnection = ConnectionManager::get('default');

// Good: Use consistent connection for related operations
$articlesTable = $this->getTableLocator()->get('Articles');
$articlesTable->setConnection($prodConnection);
$articles = $articlesTable->find()->all();
$tags = $articlesTable->Tags->find()->all(); // Uses same connection
```

### 2. Environment Detection

```php
// Use environment variables to determine connection
$connectionName = env('USE_PRODUCTION_DB') ? 'digitalocean' : 'default';
$connection = ConnectionManager::get($connectionName);
```

### 3. Error Handling

```php
try {
    $connection = ConnectionManager::get('digitalocean');
    $results = $connection->execute($query)->fetchAll();
} catch (Exception $e) {
    // Fallback to local connection
    $connection = ConnectionManager::get('default');
    $results = $connection->execute($query)->fetchAll();
}
```

### 4. Logging Connection Usage

```php
use Cake\Log\Log;

$connection = ConnectionManager::get('digitalocean');
Log::info('Using DigitalOcean database connection', ['connection' => 'digitalocean']);
```

## Summary

- **Development**: Use `default` and `test` connections for local development
- **Production Data**: Use `digitalocean` and `digitalocean_test` for production data operations
- **SSL**: DigitalOcean connections automatically use SSL with certificate verification
- **Flexibility**: Switch between connections based on environment or specific requirements
- **Testing**: All connections can be tested independently using the helper scripts