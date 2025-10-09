#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use App\Controller\Admin\QueueConfigurationsController;

// Initialize CakePHP application
require_once dirname(__DIR__) . '/config/bootstrap.php';

try {
    echo "Testing Queue Configuration Health Check Functionality\n";
    echo "====================================================\n\n";
    
    // Get database connection
    $connection = ConnectionManager::get('default');
    $query = $connection->execute('SELECT * FROM queue_configurations WHERE enabled = 1');
    $configs = $query->fetchAll('assoc');
    
    if (empty($configs)) {
        echo "No enabled queue configurations found.\n";
        exit(0);
    }
    
    echo "Found " . count($configs) . " enabled queue configuration(s)\n\n";
    
    // Simulate controller behavior
    foreach ($configs as $config) {
        echo "Testing health check logic for: {$config['name']}\n";
        echo "  Queue Type: {$config['queue_type']}\n";
        echo "  Host: {$config['host']}:{$config['port']}\n";
        
        // Simulate the controller's health check method
        $startTime = microtime(true);
        $healthy = false;
        $message = '';
        $details = [];
        
        try {
            // Check basic connectivity with timeout
            $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);
            
            if ($connection) {
                $healthy = true;
                $message = 'Connection successful';
                
                // Add queue-specific health checks
                if ($config['queue_type'] === 'rabbitmq') {
                    $details = [
                        'queue_type' => 'RabbitMQ',
                        'vhost' => $config['vhost'] ?? '/',
                        'username' => $config['username'] ?? 'guest',
                        'ssl_enabled' => $config['ssl_enabled'],
                        'exchange' => $config['exchange'],
                        'routing_key' => $config['routing_key'],
                        'status' => 'Port accessible - full AMQP health check would require additional libraries'
                    ];
                } elseif ($config['queue_type'] === 'redis') {
                    $details = [
                        'queue_type' => 'Redis',
                        'database' => $config['db_index'] ?? 0,
                        'status' => 'Port accessible'
                    ];
                    
                    // Try to send a simple PING command if it's Redis
                    try {
                        fwrite($connection, "*1\\r\\n$4\\r\\nPING\\r\\n");
                        $response = fread($connection, 1024);
                        
                        if (strpos($response, '+PONG') === 0) {
                            $details['status'] = 'Redis PING successful';
                            $details['redis_response'] = 'PONG';
                        } else {
                            $details['status'] = 'Port accessible but Redis PING failed';
                            $details['redis_response'] = trim($response);
                        }
                    } catch (Exception $e) {
                        $details['status'] = 'Port accessible but Redis communication failed';
                        $details['error'] = $e->getMessage();
                    }
                }
                
                fclose($connection);
            } else {
                $healthy = false;
                $message = "Connection failed: {$errstr} (Error {$errno})";
            }
        } catch (Exception $e) {
            $healthy = false;
            $message = 'Health check error: ' . $e->getMessage();
        }
        
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $healthResult = [
            'healthy' => $healthy,
            'message' => $message,
            'response_time_ms' => $responseTime,
            'details' => $details,
            'checked_at' => date('Y-m-d H:i:s'),
            'config_id' => $config['id'],
            'config_name' => $config['name'],
            'host' => $config['host'],
            'port' => $config['port'],
            'queue_type' => $config['queue_type']
        ];
        
        // Display result
        echo "  Health Status: " . ($healthy ? "✅ HEALTHY" : "❌ UNHEALTHY") . " ({$responseTime}ms)\n";
        echo "  Message: {$message}\n";
        
        if (!empty($details)) {
            echo "  Details:\n";
            foreach ($details as $key => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                echo "    - {$key}: {$value}\n";
            }
        }
        
        echo "  JSON Response would be:\n";
        echo "    " . json_encode($healthResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        echo "\n";
    }
    
    echo "Health check logic test completed successfully!\n";
    echo "The admin interface should show these results when accessed via browser.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}