<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

/**
 * CommentAnalyzer Class
 *
 * This class is responsible for analyzing comments using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses for comment analysis.
 */
class CommentAnalyzer extends AbstractAnthropicGenerator
{
    /**
     * Analyzes a comment using the Anthropic API.
     *
     * This method retrieves the appropriate prompt data, creates a payload,
     * sends a request to the Anthropic API, and processes the response to analyze the comment.
     *
     * @param string $comment The comment to be analyzed.
     * @return array The analysis results from the API, containing various aspects of the comment analysis.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function analyze(string $comment): array
    {
        $promptData = $this->getPromptData('comment_analysis');
        $payload = $this->createPayload($promptData, $comment);

        $result = $this->sendApiRequest($payload);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Gets the expected keys for the API response.
     *
     * @return array Array of expected response keys.
     */
    protected function getExpectedKeys(): array
    {
        return [
            'comment',
            'is_inappropriate',
            'reason',
        ];
    }

    /**
     * Gets the logger name for this analyzer.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'Comment Analyzer';
    }
}
