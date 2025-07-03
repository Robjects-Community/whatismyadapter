<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Datasource\ConnectionManager;

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
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    public static function tableExists(string $tableName): bool
    {
        // Get the default database connection
        $connection = ConnectionManager::get('default');

        $dbDatabase = null;

        if (!empty($connection->config()['database'])) {
            $dbDatabase = $connection->config()['database'];
        } else {
            return false;
        }

        // Define the query to check for the table's existence
        $query = "SELECT COUNT(*) FROM information_schema.tables 
                  WHERE table_schema = :table_schema
                  AND table_name = :table_name";

        // Execute the query with the provided table name and database schema
        $result = $connection->execute($query, [
            'table_schema' => $dbDatabase,
            'table_name' => $tableName,
        ])->fetch('assoc');

        // Check if the table exists and the database name is valid
        return !empty(array_values($result)[0]) && $dbDatabase !== false;
    }
}
