<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Api\Anthropic;

use App\Service\Api\Anthropic\AbstractAnthropicGenerator;

/**
 * Concrete test implementation of AbstractAnthropicGenerator
 * for testing the abstract base class functionality.
 */
class TestAnthropicGenerator extends AbstractAnthropicGenerator
{
    /**
     * Gets the expected keys for test purposes
     */
    protected function getExpectedKeys(): array
    {
        return ['test_key_1', 'test_key_2'];
    }

    /**
     * Gets the logger name for test purposes
     */
    protected function getLoggerName(): string
    {
        return 'Test Generator';
    }

    // Public wrapper methods for testing protected methods

    public function getPromptDataPublic(string $task): array
    {
        return $this->getPromptData($task);
    }

    public function createPayloadPublic(array $promptData, mixed $content): array
    {
        return $this->createPayload($promptData, $content);
    }

    public function ensureExpectedKeysPublic(array $result, ?array $expectedKeys = null): array
    {
        return $this->ensureExpectedKeys($result, $expectedKeys);
    }

    public function sendApiRequestPublic(array $payload, ?int $timeout = null): array
    {
        return $this->sendApiRequest($payload, $timeout);
    }

    public function formatContentPublic(mixed $content): mixed
    {
        return $this->formatContent($content);
    }
}
