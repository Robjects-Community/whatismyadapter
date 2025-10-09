<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

/**
 * SeoContentGenerator Class
 *
 * This class is responsible for generating SEO content for tags and articles
 * using the Anthropic API service. It interacts with the AI prompts table to retrieve
 * prompt data and uses the AnthropicApiService to send requests and parse responses.
 */
class SeoContentGenerator extends AbstractAnthropicGenerator
{
    /**
     * Generates SEO content for a tag.
     *
     * This method performs the following steps:
     * 1. Retrieves the appropriate prompt data for tag SEO analysis.
     * 2. Creates a payload with the tag title and description.
     * 3. Sends a request to the Anthropic API and processes the response.
     * 4. Ensures all expected SEO keys are present in the result.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content, including meta tags and social media descriptions.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        $promptData = $this->getPromptData('tag_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'tag_title' => $tagTitle,
            'tag_description' => $tagDescription,
        ]);

        $result = $this->sendApiRequest($payload);

        $defaultKeys = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
        ];

        return $this->ensureExpectedKeys($result, $defaultKeys);
    }

    /**
     * Generates SEO content for an article.
     *
     * This method performs the following steps:
     * 1. Strips HTML tags and decodes entities from the article body.
     * 2. Retrieves the appropriate prompt data for article SEO analysis.
     * 3. Creates a payload with the article title and plain text content.
     * 4. Sends a request to the Anthropic API and processes the response.
     * 5. Ensures all expected SEO keys are present in the result.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article (may contain HTML).
     * @return array The generated SEO content, including meta tags and social media descriptions.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        $plainTextContent = strip_tags(html_entity_decode($body));
        $promptData = $this->getPromptData('article_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'article_title' => $title,
            'article_content' => $plainTextContent,
        ]);

        $result = $this->sendApiRequest($payload);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Generates SEO content for an image gallery.
     *
     * This method performs the following steps:
     * 1. Retrieves the appropriate prompt data for gallery SEO analysis.
     * 2. Creates a payload with the gallery name and context information.
     * 3. Sends a request to the Anthropic API and processes the response.
     * 4. Ensures all expected SEO keys are present in the result.
     *
     * @param string $name The name of the gallery.
     * @param string $context Additional context about the gallery content and images.
     * @return array The generated SEO content, including meta tags and social media descriptions.
     * @throws \InvalidArgumentException If the task prompt data is not found.
     */
    public function generateGallerySeo(string $name, string $context): array
    {
        $promptData = $this->getPromptData('gallery_seo_analysis');
        $payload = $this->createPayload($promptData, [
            'gallery_name' => $name,
            'gallery_context' => $context,
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
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
        ];
    }

    /**
     * Gets the logger name for this generator.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'SEO Content Generator';
    }
}
