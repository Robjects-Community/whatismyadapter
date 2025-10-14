<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * AbstractJob Class
 *
 * Base class for all queue jobs providing common functionality for logging,
 * error handling, and result processing. Reduces code duplication across jobs.
 */
abstract class AbstractJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     *
     * @var int
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time
     *
     * @var bool
     */
    public static bool $shouldBeUnique = true;

    /**
     * Log the start of a job execution
     *
     * @param string $id The entity ID being processed
     * @param string $title Optional title/name for better logging
     * @return void
     */
    protected function logJobStart(string $id, string $title = ''): void
    {
        $this->log(
            sprintf('Received %s message: %s%s', static::getJobType(), $id, $title ? " : {$title}" : ''),
            'info',
            ['group_name' => static::class],
        );
    }

    /**
     * Log successful job completion
     *
     * @param string $id The entity ID that was processed
     * @param string $title Optional title/name for better logging
     * @return void
     */
    protected function logJobSuccess(string $id, string $title = ''): void
    {
        $this->log(
            sprintf('%s completed successfully. ID: %s%s', static::getJobType(), $id, $title ? " ({$title})" : ''),
            'info',
            ['group_name' => static::class],
        );
    }

    /**
     * Log job execution error
     *
     * @param string $id The entity ID that failed to process
     * @param string $error The error message
     * @param string $title Optional title/name for better logging
     * @return void
     */
    protected function logJobError(string $id, string $error, string $title = ''): void
    {
        $this->log(
            sprintf('%s failed. ID: %s%s Error: %s', static::getJobType(), $id, $title ? " ({$title})" : '', $error),
            'error',
            ['group_name' => static::class],
        );
    }

    /**
     * Execute job operation with standardized error handling and logging
     *
     * @param string $id The entity ID being processed
     * @param callable $operation The main job operation to execute
     * @param string $title Optional title/name for better logging
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    protected function executeWithErrorHandling(string $id, callable $operation, string $title = ''): ?string
    {
        $this->logJobStart($id, $title);

        try {
            $result = $operation();

            if ($result) {
                $this->logJobSuccess($id, $title);
                $this->clearContentCache();

                return Processor::ACK;
            } else {
                $this->logJobError($id, 'Operation returned false or null', $title);

                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->logJobError($id, $e->getMessage(), $title);

            return Processor::REJECT;
        }
    }

    /**
     * Execute job operation with entity save handling
     *
     * @param string $id The entity ID being processed
     * @param callable $operation Operation that should return an entity to save
     * @param string $title Optional title/name for better logging
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    protected function executeWithEntitySave(string $id, callable $operation, string $title = ''): ?string
    {
        return $this->executeWithErrorHandling($id, function () use ($operation) {
            $result = $operation();

            if ($result instanceof EntityInterface) {
                $table = $this->getTable($result->getSource());

                return $table->save($result);
            }

            return $result;
        }, $title);
    }

    /**
     * Clear content cache after successful operations
     *
     * @return void
     */
    protected function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Get table instance using TableRegistry
     *
     * @param string $tableName The table name to retrieve
     * @return \Cake\ORM\Table
     */
    protected function getTable(string $tableName): Table
    {
        return TableRegistry::getTableLocator()->get($tableName);
    }

    /**
     * Validate required message arguments
     *
     * @param \Cake\Queue\Job\Message $message The queue message
     * @param array<string> $required Array of required argument names
     * @return bool True if all required arguments are present
     */
    protected function validateArguments(Message $message, array $required): bool
    {
        foreach ($required as $arg) {
            if (!$message->getArgument($arg)) {
                $this->log(
                    sprintf('Missing required argument: %s for %s', $arg, static::getJobType()),
                    'error',
                    ['group_name' => static::class],
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    abstract protected static function getJobType(): string;

    /**
     * Execute the job with the given message
     *
     * @param \Cake\Queue\Job\Message $message The queue message
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    abstract public function execute(Message $message): ?string;
}
