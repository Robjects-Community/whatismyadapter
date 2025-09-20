<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\AbstractJob;
use Cake\ORM\Table;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * Concrete implementation of AbstractJob for testing
 */
class TestableJob extends AbstractJob
{
    protected static function getJobType(): string
    {
        return 'test job';
    }

    protected function _execute(array $data): void
    {
        // Test implementation - does nothing
    }

    public function execute(Message $message): ?string
    {
        return Processor::ACK;
    }

    // Public wrappers for testing protected methods

    public function testExecuteWithErrorHandling(string $id, callable $operation, string $title = ''): ?string
    {
        return $this->executeWithErrorHandling($id, $operation, $title);
    }

    public function testExecuteWithEntitySave(string $id, callable $operation, string $title = ''): ?string
    {
        return $this->executeWithEntitySave($id, $operation, $title);
    }

    public function testValidateArguments(Message $message, array $required): bool
    {
        return $this->validateArguments($message, $required);
    }

    public function testLogJobStart(string $id, string $title = ''): void
    {
        $this->logJobStart($id, $title);
    }

    public function testLogJobSuccess(string $id, string $title = ''): void
    {
        $this->logJobSuccess($id, $title);
    }

    public function testLogJobError(string $id, string $error, string $title = ''): void
    {
        $this->logJobError($id, $error, $title);
    }

    public function testClearContentCache(): void
    {
        $this->clearContentCache();
    }

    public function testGetTable(string $tableName): Table
    {
        return $this->getTable($tableName);
    }

    public static function getTestJobType(): string
    {
        return static::getJobType();
    }
}
