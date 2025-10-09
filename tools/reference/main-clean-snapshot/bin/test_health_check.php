#!/usr/bin/env php
<?php
/**
 * Test script for queue configuration health checking
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Container;
use Cake\Http\ServerRequestFactory;
use Cake\Core\App;
use Cake\Utility\Inflector;

// Initialize CakePHP application
require_once dirname(__DIR__) . '/config/bootstrap.php';

$container = new Container();
App::bootstrap();

try {
    // Get the queue configurations table
    $connection = ConnectionManager::get('default');
    $query = $connection->execute('SELECT * FROM queue_configurations WHERE enabled = 1');
    $configs = $query->fetchAll('assoc');
    
    if (empty($configs)) {
        echo "No enabled queue configurations found.\n";
        exit(1);
    }
    
    echo "Testing health checks for " . count($configs) . " queue configuration(s):\n\n";
    
    foreach ($configs as $config) {
        echo "Testing: {$config['name']} ({$config['queue_type']})\n";
        echo "  Host: {$config['host']}:{$config['port']}\n";
        
        // Test basic connectivity
        $startTime = microtime(true);
        $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($connection) {
            echo "  Status: ✅ HEALTHY ({$responseTime}ms)\n";
            
            // Test Redis-specific functionality
            if ($config['queue_type'] === 'redis') {
                try {
                    fwrite($connection, "*1\r\n$4\r\nPING\r\n");
                    $response = fread($connection, 1024);
                    
                    if (strpos($response, '+PONG') === 0) {
                        echo "  Redis: ✅ PING successful\n";
                    } else {
                        echo "  Redis: ⚠️  Port accessible but PING failed\n";
                    }
                } catch (Exception $e) {
                    echo "  Redis: ⚠️  Communication error: " . $e->getMessage() . "\n";
                }
            }
            
            fclose($connection);
        } else {
            echo "  Status: ❌ UNHEALTHY - {$errstr} (Error {$errno})\n";
        }
        
        echo "\n";
    }
    
    echo "Health check test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}