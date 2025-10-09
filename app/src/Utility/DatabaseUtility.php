<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Datasource\ConnectionManager;
use Cake\Database\Driver\Mysql as MysqlDriver;
use Cake\Database\Driver\Postgres as PostgresDriver;
use Cake\Database\Driver\Sqlite as SqliteDriver;
use Throwable;

/**
 * Class DatabaseUtility
 *
 * This utility class provides a method to check if a table exists in the database.
 */
class DatabaseUtility
{
    /**
     * Check if a table exists in the database.
     *
     * Engine-aware implementation that supports SQLite, MySQL and Postgres.
     * Falls back to Cake's schema collection when driver is unknown.
     *
     * @param string $tableName The name of the table to check.
     * @param string|null $connectionName Optional connection name (defaults to 'default').
     * @return bool True if the table exists, false otherwise.
     */
    public static function tableExists(string $tableName, ?string $connectionName = null): bool
    {
        $connectionName = $connectionName ?? 'default';

        try {
            $connection = ConnectionManager::get($connectionName);
        } catch (\Throwable $e) {
            return false;
        }

        $driver = $connection->getDriver();

        try {
            // SQLite: use sqlite_master
            if ($driver instanceof SqliteDriver) {
                $row = $connection
                    ->execute(
                        "SELECT name FROM sqlite_master WHERE type='table' AND name = :table_name",
                        ['table_name' => $tableName]
                    )
                    ->fetch('assoc');

                return !empty($row);
            }

            // MySQL: information_schema
            if ($driver instanceof MysqlDriver) {
                // Get current database name
                $config = method_exists($connection, 'getConfig') ? $connection->getConfig() : $connection->config();
                $dbDatabase = $config['database'] ?? null;

                if (!$dbDatabase) {
                    // Fallback to SELECT DATABASE() if not configured
                    $dbDatabase = $connection->execute('SELECT DATABASE()')->fetchColumn(0);
                }

                if (!$dbDatabase) {
                    return false;
                }

                $result = $connection
                    ->execute(
                        'SELECT COUNT(*) AS cnt FROM information_schema.tables
                         WHERE table_schema = :table_schema AND table_name = :table_name',
                        [
                            'table_schema' => $dbDatabase,
                            'table_name' => $tableName,
                        ]
                    )
                    ->fetch('assoc');

                return ((int)($result['cnt'] ?? 0)) > 0;
            }

            // Postgres: information_schema with current_schema()
            if ($driver instanceof PostgresDriver) {
                $result = $connection
                    ->execute(
                        'SELECT EXISTS (
                             SELECT 1 FROM information_schema.tables
                             WHERE table_schema = current_schema() AND table_name = :table_name
                         ) AS exists',
                        ['table_name' => $tableName]
                    )
                    ->fetch('assoc');

                $exists = $result['exists'] ?? $result['exists?'] ?? null;
                return (bool)$exists;
            }

            // Generic fallback: rely on schema collection listing
            $schema = $connection->getSchemaCollection();
            $tables = $schema->listTables();
            return in_array($tableName, $tables, true);
        } catch (Throwable $e) {
            // Be conservative: on any error, assume table is absent
            return false;
        }
    }
}
