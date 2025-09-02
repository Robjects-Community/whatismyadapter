<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

/**
 * ArticleTagsGenerator
 *
 * This class is responsible for generating article tags using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses.
 */
class ArticleTagsGenerator extends AbstractAnthropicGenerator
{
    /**
     * Generates article tags based on the provided title and body content.
     *
     * This method retrieves the appropriate prompt data, creates a payload,
     * sends a request to the Anthropic API, and processes the response to generate tags.
     *
     * @param array $allTags An array of existing tags.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated tags for the article.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateArticleTags(array $allTags, string $title, string $body): array
    {
        $promptData = $this->getPromptData('article_tag_generation');
        $payload = $this->createPayload($promptData, [
            'existing_tags' => $allTags,
            'article_title' => $title,
            'article_content' => $body,
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
        return ['tags'];
    }

    /**
     * Gets the logger name for this generator.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'Article Tag Generator';
    }
}
