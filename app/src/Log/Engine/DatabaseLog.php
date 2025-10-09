<?php
declare(strict_types=1);

namespace App\Log\Engine;

use Cake\Log\Engine\BaseLog;
use Cake\ORM\TableRegistry;
use Exception;
use Stringable;

/**
 * DatabaseLog
 *
 * A logging engine that writes log messages to a database table.
 * This class extends the BaseLog class and implements the log method
 * to save log entries into the 'SystemLogs' table.
 */
class DatabaseLog extends BaseLog
{
    /**
     * Logs a message to the database and falls back to file logging on failure.
     *
     * This method attempts to log a message with a specified level and context to the 'SystemLogs' database table.
     * If the database logging fails, it logs the error details to a file. Additionally, any exceptions encountered
     * during the logging process are also logged to a file.
     *
     * @param mixed $level The severity level of the log message. This can be any type that represents the log level.
     * @param \Stringable|string $message The log message to be recorded. This can be a string or an object that implements
     *                                   the Stringable interface.
     * @param array $context An array of additional context information to include with the log message. The context
     *                       can contain any additional data that should be logged alongside the message. The 'group_name'
     *                       key is used to categorize the log entry, defaulting to 'general' if not provided.
     * @return void
     * @throws \Exception If an error occurs while attempting to log the message to the database, the exception is caught
     *                    and its message is logged to a file.
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $group = $context['group_name'] ?? 'general';

        $data = [
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'created' => date('Y-m-d H:i:s'),
            'group_name' => $group,
        ];

        try {
            $logsTable = TableRegistry::getTableLocator()->get('SystemLogs');
            $logEntry = $logsTable->newEntity($data);
            if (!$logsTable->save($logEntry)) {
                // Log the error to a file if database logging fails
                file_put_contents(
                    LOGS . 'database_log_errors.log',
                    date('Y-m-d H:i:s') . ': Failed to save log entry: ' . json_encode($logEntry->getErrors()) . "\n",
                    FILE_APPEND,
                );
            }
        } catch (Exception $e) {
            // Log any exceptions to a file
            file_put_contents(
                LOGS . 'database_log_errors.log',
                date('Y-m-d H:i:s') . ': Exception occurred while logging: ' . $e->getMessage() . "\n",
                FILE_APPEND,
            );
        }
    }
}
