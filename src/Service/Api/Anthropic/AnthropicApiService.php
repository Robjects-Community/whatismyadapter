<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AbstractApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\ORM\TableRegistry;

/**
 * AnthropicApiService Class
 *
 * This service class provides an interface to interact with the Anthropic API,
 * handling various AI-related tasks such as SEO content generation, image analysis,
 * comment moderation, and text summarization.
 */
class AnthropicApiService extends AbstractApiService
{
    /**
     * The base URL for the Anthropic API.
     *
     * @var string
     */
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * The version of the Anthropic API being used.
     *
     * @var string
     */
    private const API_VERSION = '2023-06-01';

    /**
     * @var \App\Model\Table\AipromptsTable The table instance for AI prompts.
     */
    private AipromptsTable $aipromptsTable;

    /**
     * @var \App\Service\Api\Anthropic\SeoContentGenerator The SEO content generator service.
     */
    private SeoContentGenerator $seoContentGenerator;

    /**
     * @var \App\Service\Api\Anthropic\ImageAnalyzer The image analyzer service.
     */
    private ImageAnalyzer $imageAnalyzer;

    /**
     * @var \App\Service\Api\Anthropic\CommentAnalyzer The comment analyzer service.
     */
    private CommentAnalyzer $commentAnalyzer;

    /**
     * @var \App\Service\Api\Anthropic\ArticleTagsGenerator The article tags generator service.
     */
    private ArticleTagsGenerator $articleTagsGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TextSummaryGenerator The text summary generator service.
     */
    private TextSummaryGenerator $textSummaryGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TranslationGenerator The text summary generator service.
     */
    private TranslationGenerator $translationGenerator;

    /**
     * AnthropicApiService constructor.
     *
     * Initializes the service with necessary dependencies and configurations.
     */
    public function __construct()
    {
        $apiKey = SettingsManager::read('Anthropic.apiKey');
        parent::__construct(new Client(), $apiKey, self::API_URL, self::API_VERSION);

        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');
        $this->seoContentGenerator = new SeoContentGenerator($this, $this->aipromptsTable);
        $this->imageAnalyzer = new ImageAnalyzer($this, $this->aipromptsTable);
        $this->commentAnalyzer = new CommentAnalyzer($this, $this->aipromptsTable);
        $this->articleTagsGenerator = new ArticleTagsGenerator($this, $this->aipromptsTable);
        $this->textSummaryGenerator = new TextSummaryGenerator($this, $this->aipromptsTable);
        $this->translationGenerator = new TranslationGenerator($this, $this->aipromptsTable);
    }

    /**
     * Gets the headers for the API request.
     *
     * @return array An associative array of headers.
     */
    protected function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Generates SEO content for a tag.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content.
     */
    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        return $this->seoContentGenerator->generateTagSeo($tagTitle, $tagDescription);
    }

    /**
     * Generates SEO content for an article.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated SEO content.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        return $this->seoContentGenerator->generateArticleSeo($title, $body);
    }

    /**
     * Generates SEO content for an image gallery.
     *
     * @param string $name The name of the gallery.
     * @param string $context Additional context about the gallery content and images.
     * @return array The generated SEO content.
     */
    public function generateGallerySeo(string $name, string $context): array
    {
        return $this->seoContentGenerator->generateGallerySeo($name, $context);
    }

    /**
     * Generates tags for an article.
     *
     * @param array $allTags All available tags.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated article tags.
     */
    public function generateArticleTags(array $allTags, string $title, string $body): array
    {
        return $this->articleTagsGenerator->generateArticleTags($allTags, $title, $body);
    }

    /**
     * Analyzes an image.
     *
     * @param string $imagePath The path to the image file.
     * @return array The analysis results.
     */
    public function analyzeImage(string $imagePath): array
    {
        return $this->imageAnalyzer->analyze($imagePath);
    }

    /**
     * Analyzes a comment.
     *
     * @param string $comment The comment text to analyze.
     * @return array The analysis results.
     */
    public function analyzeComment(string $comment): array
    {
        return $this->commentAnalyzer->analyze($comment);
    }

    /**
     * Generates a summary for a given text.
     *
     * @param string $context The context of the text (e.g., 'article', 'page', 'report').
     * @param string $text The text to summarize.
     * @return array The generated summary.
     */
    public function generateTextSummary(string $context, string $text): array
    {
        return $this->textSummaryGenerator->generateTextSummary($context, $text);
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * This method utilizes the TranslationGenerator service to perform translations
     * of the provided strings from the specified source locale to the target locale.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en_US').
     * @param string $localeTo The locale code of the target language (e.g., 'fr_FR').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        return $this->translationGenerator->generateTranslation($strings, $localeFrom, $localeTo);
    }

    /**
     * Parses the response from the API.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data.
     */
    public function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();

        return json_decode($responseData['content'][0]['text'], true);
    }
}
