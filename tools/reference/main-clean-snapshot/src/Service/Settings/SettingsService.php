<?php
declare(strict_types=1);

namespace App\Service\Settings;

use Cake\Database\Exception\DatabaseException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Service for managing application settings
 * 
 * Provides a simple interface to store and retrieve key-value pairs
 * organized by namespace. Settings are stored as JSON in the database
 * and can be merged with Configure at runtime.
 */
class SettingsService
{
    protected $connection;
    protected $table = 'system_settings';

    public function __construct()
    {
        $this->connection = ConnectionManager::get('default');
    }

    /**
     * Get a setting value
     *
     * @param string $namespace Setting namespace (e.g., 'quiz', 'forms')
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $namespace, string $key, $default = null)
    {
        try {
            $query = $this->connection->selectQuery()
                ->select(['value_json'])
                ->from($this->table)
                ->where([
                    'namespace' => $namespace,
                    'setting_key' => $key
                ])
                ->limit(1);

            $result = $query->execute()->fetch('assoc');
            
            if ($result) {
                return json_decode($result['value_json'], true);
            }
            
            return $default;
        } catch (DatabaseException $e) {
            Log::error('SettingsService: Failed to get setting', [
                'namespace' => $namespace, 
                'key' => $key, 
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Set a setting value
     *
     * @param string $namespace Setting namespace
     * @param string $key Setting key
     * @param mixed $value Value to store
     * @return bool Success
     */
    public function set(string $namespace, string $key, $value): bool
    {
        try {
            $valueJson = json_encode($value);
            $now = date('Y-m-d H:i:s');

            // Try to update first
            $updateQuery = $this->connection->updateQuery()
                ->update($this->table)
                ->set([
                    'value_json' => $valueJson,
                    'modified' => $now
                ])
                ->where([
                    'namespace' => $namespace,
                    'setting_key' => $key
                ]);

            $affectedRows = $updateQuery->execute()->rowCount();

            // If no rows updated, insert new record
            if ($affectedRows === 0) {
                $insertQuery = $this->connection->insertQuery()
                    ->insert(['namespace', 'setting_key', 'value_json', 'created', 'modified'])
                    ->into($this->table)
                    ->values([
                        'namespace' => $namespace,
                        'setting_key' => $key,
                        'value_json' => $valueJson,
                        'created' => $now,
                        'modified' => $now
                    ]);

                $insertQuery->execute();
            }

            return true;
        } catch (DatabaseException $e) {
            Log::error('SettingsService: Failed to set setting', [
                'namespace' => $namespace, 
                'key' => $key, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all settings for a namespace
     *
     * @param string $namespace Setting namespace
     * @return array Associative array of key => value pairs
     */
    public function getAll(string $namespace): array
    {
        try {
            $query = $this->connection->selectQuery()
                ->select(['setting_key', 'value_json'])
                ->from($this->table)
                ->where(['namespace' => $namespace]);

            $results = $query->execute()->fetchAll('assoc');
            
            $settings = [];
            foreach ($results as $result) {
                $settings[$result['setting_key']] = json_decode($result['value_json'], true);
            }
            
            return $settings;
        } catch (DatabaseException $e) {
            Log::error('SettingsService: Failed to get all settings', [
                'namespace' => $namespace, 
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Delete a specific setting
     *
     * @param string $namespace Setting namespace
     * @param string $key Setting key
     * @return bool Success
     */
    public function delete(string $namespace, string $key): bool
    {
        try {
            $deleteQuery = $this->connection->deleteQuery()
                ->delete($this->table)
                ->where([
                    'namespace' => $namespace,
                    'setting_key' => $key
                ]);

            $deleteQuery->execute();
            return true;
        } catch (DatabaseException $e) {
            Log::error('SettingsService: Failed to delete setting', [
                'namespace' => $namespace, 
                'key' => $key, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete all settings for a namespace
     *
     * @param string $namespace Setting namespace
     * @return bool Success
     */
    public function deleteNamespace(string $namespace): bool
    {
        try {
            $deleteQuery = $this->connection->deleteQuery()
                ->delete($this->table)
                ->where(['namespace' => $namespace]);

            $deleteQuery->execute();
            return true;
        } catch (DatabaseException $e) {
            Log::error('SettingsService: Failed to delete namespace', [
                'namespace' => $namespace, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Merge database settings into Configure for a namespace
     * This allows DB settings to override config file defaults
     *
     * @param string $namespace Setting namespace
     * @param string $configKey Configure key to merge into (e.g., 'Quiz')
     * @return bool Success
     */
    public function mergeIntoConfigure(string $namespace, string $configKey): bool
    {
        try {
            $dbSettings = $this->getAll($namespace);
            
            if (!empty($dbSettings)) {
                // Get existing configure data
                $existing = Configure::read($configKey) ?: [];
                
                // Recursively merge DB settings over existing config
                $merged = $this->arrayMergeRecursive($existing, $dbSettings);
                
                // Update Configure
                Configure::write($configKey, $merged);
                
                Log::debug("SettingsService: Merged {$namespace} settings into Configure.{$configKey}", [
                    'merged_keys' => array_keys($dbSettings)
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('SettingsService: Failed to merge into Configure', [
                'namespace' => $namespace, 
                'configKey' => $configKey, 
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if settings table exists
     *
     * @return bool
     */
    public function tableExists(): bool
    {
        try {
            $schema = $this->connection->getSchemaCollection();
            return in_array($this->table, $schema->listTables());
        } catch (DatabaseException $e) {
            return false;
        }
    }

    /**
     * Recursively merge arrays, allowing nested structure override
     *
     * @param array $array1 Base array
     * @param array $array2 Override array
     * @return array Merged result
     */
    private function arrayMergeRecursive(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}