<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

/**
 * Class TextSummaryGenerator
 *
 * This class is responsible for generating summaries of text using the Anthropic
 * API service. It interacts with the AI prompts table to retrieve prompt data
 * and uses the AnthropicApiService to send requests and parse responses.
 */
class TextSummaryGenerator extends AbstractAnthropicGenerator
{
    /**
     * Generates a text summary using the Anthropic API.
     *
     * @param string $context The context for the text summary.
     * @param string $text The text to be summarized.
     * @return array The generated summary and key points.
     */
    public function generateTextSummary(string $context, string $text): array
    {
        $promptData = $this->getPromptData('text_summary');
        $payload = $this->createPayload($promptData, [
            'context' => $context,
            'text' => $text,
        ]);

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
            'summary',
            'key_points',
            'lede',
        ];
    }

    /**
     * Gets the logger name for this generator.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'Text Summary Generator';
    }
}
