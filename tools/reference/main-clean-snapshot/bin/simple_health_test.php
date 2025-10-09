#!/usr/bin/env php
<?php

// Simple test without complex CakePHP bootstrap
$dsn = 'mysql:host=mysql;dbname=cms;charset=utf8mb4';
$user = 'cms_user';
$password = 'password';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Database connection successful!\n\n";
    
    $stmt = $pdo->query('SELECT id, name, queue_type, host, port, enabled FROM queue_configurations');
    $configs = $stmt->fetchAll();
    
    if (empty($configs)) {
        echo "No queue configurations found.\n";
        exit(0);
    }
    
    echo "Found " . count($configs) . " queue configuration(s):\n\n";
    
    foreach ($configs as $config) {
        echo "Testing: {$config['name']} ({$config['queue_type']})\n";
        echo "  Host: {$config['host']}:{$config['port']}\n";
        echo "  Status: " . ($config['enabled'] ? 'Enabled' : 'Disabled') . "\n";
        
        if ($config['enabled']) {
            // Test connectivity
            $startTime = microtime(true);
            $connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($connection) {
                echo "  Health: ✅ HEALTHY ({$responseTime}ms)\n";
                
                // Test Redis PING if it's a Redis connection
                if ($config['queue_type'] === 'redis') {
                    fwrite($connection, "*1\r\n$4\r\nPING\r\n");
                    $response = fread($connection, 1024);
                    
                    if (strpos($response, '+PONG') === 0) {
                        echo "  Redis: ✅ PING successful\n";
                    } else {
                        echo "  Redis: ⚠️  PING failed\n";
                    }
                }
                
                fclose($connection);
            } else {
                echo "  Health: ❌ UNHEALTHY - Connection failed: {$errstr} (Error {$errno})\n";
            }
        }
        echo "\n";
    }
    
    echo "Health check completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}