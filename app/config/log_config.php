<?php
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

/**
 * Establishes a connection to the default datasource, 
 *
 * @var \Cake\Datasource\ConnectionInterface $connection
 */
$configuredDatasources = ConnectionManager::configured();

if (!empty($configuredDatasources)) {
    $connection = ConnectionManager::get('default');

    /**
     * Reads the list of database tables from the cache.
     * If the cache is empty, retrieves the list from the database and writes it to the cache.
     *
     * @var array|null $tables List of database tables or null if not cached.
     */
    $cacheKey = 'database_tables';
    $tables = Cache::read($cacheKey, 'default');

    if (!$tables) {
        /**
         * Retrieves the list of tables from the database schema collection.
         *
         * @var array $tables List of database tables.
         */
        $tables = $connection->getSchemaCollection()->listTables();

        /**
         * Writes the list of tables to the cache.
         */
        Cache::write($cacheKey, $tables, 'default');
    }

    /**
     * Configuration array for logging.
     *
     * @var array $logConfig
     */
    $logConfig = [];

    /**
     * Checks if the 'system_logs' table exists in the database.
     * If it exists, configures the logging settings for the 'system' log.
     */
    if (in_array('cms', $tables)) {
        $logConfig['cms'] = [
            'className' => 'App\Log\Engine\DatabaseLog',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency', 'notice', 'info', 'debug'],
        ];
    }

    // Apply the configuration
    foreach ($logConfig as $name => $config) {
        Log::setConfig($name, $config);
    }
}