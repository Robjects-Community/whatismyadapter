#!/usr/bin/env php
<?php
/**
 * Queue Configuration Reader
 * 
 * This script reads queue configurations from the database and outputs 
 * worker command parameters. Used by Docker services to get dynamic config.
 * 
 * Usage: php bin/get_queue_config.php [config_key] [field]
 * 
 * Examples:
 *   php bin/get_queue_config.php image_analysis config_key  # Returns: rabbitmq
 *   php bin/get_queue_config.php image_analysis queue_name  # Returns: image_analysis  
 *   php bin/get_queue_config.php fast config_key           # Returns: fast
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/bootstrap.php';

use Cake\ORM\TableRegistry;

function getQueueConfig($configKey, $field = null)
{
    try {
        $queueTable = TableRegistry::getTableLocator()->get('QueueConfigurations');
        
        $config = $queueTable->find()
            ->where([
                'config_key' => $configKey,
                'enabled' => true
            ])
            ->first();
        
        if (!$config) {
            throw new Exception("Queue configuration '{$configKey}' not found or disabled");
        }
        
        if ($field) {
            return $config->get($field) ?? '';
        }
        
        return $config;
    } catch (Exception $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        exit(1);
    }
}

// Get command line arguments
$configKey = $argv[1] ?? null;
$field = $argv[2] ?? null;

if (!$configKey) {
    fwrite(STDERR, "Usage: php bin/get_queue_config.php [config_key] [field]\n");
    fwrite(STDERR, "Examples:\n");
    fwrite(STDERR, "  php bin/get_queue_config.php image_analysis config_key\n");
    fwrite(STDERR, "  php bin/get_queue_config.php image_analysis queue_name\n");
    exit(1);
}

// Handle special cases for worker command building
if ($field === 'worker_command') {
    $config = getQueueConfig($configKey);
    
    // Build the appropriate worker command
    $baseCommand = "bin/cake worker";
    
    // Add config parameter
    $baseCommand .= " --config=" . $config->config_key;
    
    // Add queue parameter  
    $baseCommand .= " --queue=" . $config->queue_name;
    
    // Add verbose flag
    $baseCommand .= " --verbose";
    
    echo $baseCommand;
    exit(0);
}

if ($field === 'wait_command') {
    $config = getQueueConfig($configKey);
    
    // Build wait command based on queue type
    if ($config->queue_type === 'rabbitmq') {
        echo "while ! nc -z {$config->host} {$config->port}; do echo 'Waiting for RabbitMQ ({$config->host}:{$config->port})...'; sleep 3; done";
    } elseif ($config->queue_type === 'redis') {
        echo "while ! nc -z {$config->host} {$config->port}; do echo 'Waiting for Redis ({$config->host}:{$config->port})...'; sleep 3; done";
    }
    exit(0);
}

if ($field === 'ready_message') {
    $config = getQueueConfig($configKey);
    echo ucfirst($config->queue_type) . " ({$config->name}) is ready!";
    exit(0);
}

// Return specific field or entire config
$result = getQueueConfig($configKey, $field);

if (is_string($result) || is_numeric($result)) {
    echo $result;
} else {
    echo json_encode($result);
}