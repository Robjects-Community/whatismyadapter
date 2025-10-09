<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use Cake\Log\LogTrait;
use InvalidArgumentException;

/**
 * AbstractAnthropicGenerator
 *
 * Base class for all Anthropic API generators providing common functionality
 * for prompt retrieval, payload creation, and response validation.
 */
abstract class AbstractAnthropicGenerator
{
    use LogTrait;

    /**
     * The Anthropic API service used for sending requests and parsing responses.
     *
     * @var \App\Service\Api\Anthropic\AnthropicApiService
     */
    protected AnthropicApiService $apiService;

    /**
     * The AI prompts table for retrieving prompt data necessary for generation.
     *
     * @var \App\Model\Table\AipromptsTable
     */
    protected AipromptsTable $aipromptsTable;

    /**
     * AbstractAnthropicGenerator constructor.
     *
     * Initializes the API service and AI prompts table.
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService $apiService The Anthropic API service.
     * @param \App\Model\Table\AipromptsTable $aipromptsTable The AI prompts table.
     */
    public function __construct(AnthropicApiService $apiService, AipromptsTable $aipromptsTable)
    {
        $this->apiService = $apiService;
        $this->aipromptsTable = $aipromptsTable;
    }

    /**
     * Retrieves prompt data for a specific task from the AI prompts table.
     *
     * @param string $task The task type for which to retrieve prompt data.
     * @return array The retrieved prompt data.
     * @throws \InvalidArgumentException If the task is unknown or not found.
     */
    protected function getPromptData(string $task): array
    {
        $prompt = $this->aipromptsTable->find()
            ->where(['task_type' => $task])
            ->first();

        if (!$prompt) {
            throw new InvalidArgumentException("Unknown task: {$task}");
        }

        return [
            'system_prompt' => $prompt->system_prompt,
            'model' => $prompt->model,
            'max_tokens' => $prompt->max_tokens,
            'temperature' => $prompt->temperature,
        ];
    }

    /**
     * Creates a basic payload for the API request using the provided prompt data and content.
     *
     * This method handles the standard payload structure. Child classes can override
     * this method for specialized payload requirements (e.g., image data).
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param mixed $content The content to be included in the payload.
     * @return array The created payload for the API request.
     */
    protected function createPayload(array $promptData, mixed $content): array
    {
        return [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->formatContent($content),
                ],
            ],
        ];
    }

    /**
     * Formats content for inclusion in the API payload.
     *
     * Most generators use JSON encoding for arrays, plain strings for text.
     * Child classes can override for specialized formatting.
     *
     * @param mixed $content The content to format.
     * @return mixed The formatted content.
     */
    protected function formatContent(mixed $content): mixed
    {
        return is_array($content) ? json_encode($content) : $content;
    }

    /**
     * Ensures that the result contains all expected keys, initializing them if necessary.
     *
     * @param array $result The result array to check and modify.
     * @param array|null $expectedKeys Optional array of expected keys. If null, uses getExpectedKeys().
     * @return array The result array with all expected keys initialized.
     */
    protected function ensureExpectedKeys(array $result, ?array $expectedKeys = null): array
    {
        $keys = $expectedKeys ?? $this->getExpectedKeys();

        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = '';
                $this->log(
                    sprintf('%s did not find expected key: %s', $this->getLoggerName(), $key),
                    'error',
                    ['group_name' => 'anthropic'],
                );
            }
        }

        return $result;
    }

    /**
     * Sends a request to the API with optional timeout override.
     *
     * @param array $payload The payload to send.
     * @param int|null $timeout Optional timeout override.
     * @return array The parsed response.
     */
    protected function sendApiRequest(array $payload, ?int $timeout = null): array
    {
        $response = $timeout !== null
            ? $this->apiService->sendRequest($payload, $timeout)
            : $this->apiService->sendRequest($payload);

        return $this->apiService->parseResponse($response);
    }

    /**
     * Gets the expected keys for the API response.
     * Each generator must define what keys it expects from the API.
     *
     * @return array Array of expected response keys.
     */
    abstract protected function getExpectedKeys(): array;

    /**
     * Gets the logger name for this generator.
     * Used in error logging to identify which generator logged the message.
     *
     * @return string The logger name.
     */
    abstract protected function getLoggerName(): string;
}
