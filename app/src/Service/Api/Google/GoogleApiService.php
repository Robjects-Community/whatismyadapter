<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Service\Api\AiMetricsService;
use App\Service\Api\RateLimitService;
use App\Utility\SettingsManager;
use Cake\Core\Configure;
use Exception;
use Google\Cloud\Translate\V2\TranslateClient;
use InvalidArgumentException;

/**
 * Service class for interacting with the Google Cloud Translate API.
 */
class GoogleApiService
{
    /**
     * The Google Cloud Translate client instance.
     *
     * @var \Google\Cloud\Translate\V2\TranslateClient
     */
    private TranslateClient $translateClient;

    /**
     * @var array<string, string>
     */
    private array $preservedBlocks = [];

    /**
     * @var \App\Service\Api\AiMetricsService
     */
    private AiMetricsService $metricsService;

    /**
     * Constructor for the GoogleApiService class.
     * Initializes the Google Cloud Translate client with the API key from the settings.
     */
    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => SettingsManager::read('Google.translateApiKey', Configure::read('TRANSLATE_API_KEY', '')),
        ]);

        $this->metricsService = new AiMetricsService();
    }

    /**
     * Translates an array of strings from one language to another using batch translation.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The source language locale code (e.g., 'en' for English).
     * @param string $localeTo The target language locale code (e.g., 'fr' for French).
     * @return array An array containing the translated strings and their original values.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        if (empty($strings)) {
            return ['translations' => []];
        }

        // Google Translate API has batch size limits
        if (count($strings) > 100) {
            throw new InvalidArgumentException('Batch size exceeds Google API limit');
        }

        return $this->executeWithMetrics(
            'google_translate_strings',
            $strings,
            function () use ($strings, $localeFrom, $localeTo) {
                $results = $this->translateClient->translateBatch($strings, [
                    'source' => $localeFrom,
                    'target' => $localeTo,
                ]);

                $translatedStrings = [];

                foreach ($results as $result) {
                    $translatedStrings['translations'][] = [
                        'original' => $result['input'],
                        'translated' => $result['text'],
                    ];
                }

                return $translatedStrings;
            },
        );
    }

    /**
     * Translates an article title and body into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the article to be translated.
     * @param string $body The body of the article to be translated (can contain HTML).
     * @param string $summary The summary of the article to be translated.
     * @param string $meta_title The meta title of the article to be translated.
     * @param string $meta_description The meta description of the article to be translated.
     * @param string $meta_keywords The meta keywords of the article to be translated.
     * @param string $facebook_description The Facebook description of the article to be translated.
     * @param string $linkedin_description The LinkedIn description of the article to be translated.
     * @param string $instagram_description The Instagram description of the article to be translated.
     * @param string $twitter_description The Twitter description of the article to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateArticle(
        string $title,
        string $lede,
        string $body,
        string $summary,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        $contentArray = [
            $title,
            $lede,
            $body,
            $summary,
            $meta_title,
            $meta_description,
            $meta_keywords,
            $facebook_description,
            $linkedin_description,
            $instagram_description,
            $twitter_description,
        ];

        return $this->executeWithMetrics(
            'google_translate_article',
            $contentArray,
            function () use ($title, $lede, $body, $summary, $meta_title, $meta_description, $meta_keywords, $facebook_description, $linkedin_description, $instagram_description, $twitter_description) {
                $locales = array_filter(SettingsManager::read('Translations', []));

                $this->preservedBlocks = [];
                $processedBody = $this->preprocessContent($body);

                $translations = [];
                foreach ($locales as $locale => $enabled) {
                    $translationResult = $this->translateClient->translateBatch(
                        [
                            $title,
                            $lede,
                            $processedBody,
                            $summary,
                            $meta_title,
                            $meta_description,
                            $meta_keywords,
                            $facebook_description,
                            $linkedin_description,
                            $instagram_description,
                            $twitter_description,
                        ],
                        [
                            'source' => 'en',
                            'target' => $locale,
                            'format' => 'html',
                        ],
                    );
                    $translations[$locale]['title'] = $translationResult[0]['text'];
                    $translations[$locale]['lede'] = $translationResult[1]['text'];
                    $translations[$locale]['body'] = $this->postprocessContent($translationResult[2]['text']);
                    $translations[$locale]['summary'] = $translationResult[3]['text'];
                    $translations[$locale]['meta_title'] = $translationResult[4]['text'];
                    $translations[$locale]['meta_description'] = $translationResult[5]['text'];
                    $translations[$locale]['meta_keywords'] = $translationResult[6]['text'];
                    $translations[$locale]['facebook_description'] = $translationResult[7]['text'];
                    $translations[$locale]['linkedin_description'] = $translationResult[8]['text'];
                    $translations[$locale]['instagram_description'] = $translationResult[9]['text'];
                    $translations[$locale]['twitter_description'] = $translationResult[10]['text'];
                }

                return $translations;
            },
        );
    }

    /**
     * Translates a tag title and description into multiple languages using the Google Translate API.
     *
     * @param string $title The title of the tag to be translated.
     * @param string $description The description of the tag to be translated.
     * @param string $meta_title The meta title of the tag to be translated.
     * @param string $meta_description The meta description of the tag to be translated.
     * @param string $meta_keywords The meta keywords of the tag to be translated.
     * @param string $facebook_description The Facebook description of the tag to be translated.
     * @param string $linkedin_description The LinkedIn description of the tag to be translated.
     * @param string $instagram_description The Instagram description of the tag to be translated.
     * @param string $twitter_description The Twitter description of the tag to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateTag(
        string $title,
        string $description,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        $contentArray = [
            $title,
            $description,
            $meta_title,
            $meta_description,
            $meta_keywords,
            $facebook_description,
            $linkedin_description,
            $instagram_description,
            $twitter_description,
        ];

        return $this->executeWithMetrics(
            'google_translate_tag',
            $contentArray,
            function () use ($title, $description, $meta_title, $meta_description, $meta_keywords, $facebook_description, $linkedin_description, $instagram_description, $twitter_description) {
                $locales = array_filter(SettingsManager::read('Translations', []));

                $translations = [];
                foreach ($locales as $locale => $enabled) {
                    $translationResult = $this->translateClient->translateBatch(
                        [
                            $title,
                            $description,
                            $meta_title,
                            $meta_description,
                            $meta_keywords,
                            $facebook_description,
                            $linkedin_description,
                            $instagram_description,
                            $twitter_description,
                        ],
                        [
                            'source' => 'en',
                            'target' => $locale,
                        ],
                    );
                    $translations[$locale]['title'] = $translationResult[0]['text'];
                    $translations[$locale]['description'] = $translationResult[1]['text'];
                    $translations[$locale]['meta_title'] = $translationResult[2]['text'];
                    $translations[$locale]['meta_description'] = $translationResult[3]['text'];
                    $translations[$locale]['meta_keywords'] = $translationResult[4]['text'];
                    $translations[$locale]['facebook_description'] = $translationResult[5]['text'];
                    $translations[$locale]['linkedin_description'] = $translationResult[6]['text'];
                    $translations[$locale]['instagram_description'] = $translationResult[7]['text'];
                    $translations[$locale]['twitter_description'] = $translationResult[8]['text'];
                }

                return $translations;
            },
        );
    }

    /**
     * Translates an image gallery name and description into multiple languages using the Google Translate API.
     *
     * @param string $name The name of the gallery to be translated.
     * @param string $description The description of the gallery to be translated.
     * @param string $meta_title The meta title of the gallery to be translated.
     * @param string $meta_description The meta description of the gallery to be translated.
     * @param string $meta_keywords The meta keywords of the gallery to be translated.
     * @param string $facebook_description The Facebook description of the gallery to be translated.
     * @param string $linkedin_description The LinkedIn description of the gallery to be translated.
     * @param string $instagram_description The Instagram description of the gallery to be translated.
     * @param string $twitter_description The Twitter description of the gallery to be translated.
     * @return array An associative array where the keys are the locale codes and the values are arrays
     *               containing the translated fields for each enabled locale.
     */
    public function translateImageGallery(
        string $name,
        string $description,
        string $meta_title,
        string $meta_description,
        string $meta_keywords,
        string $facebook_description,
        string $linkedin_description,
        string $instagram_description,
        string $twitter_description,
    ): array {
        $contentArray = [
            $name,
            $description,
            $meta_title,
            $meta_description,
            $meta_keywords,
            $facebook_description,
            $linkedin_description,
            $instagram_description,
            $twitter_description,
        ];

        return $this->executeWithMetrics(
            'google_translate_gallery',
            $contentArray,
            function () use ($name, $description, $meta_title, $meta_description, $meta_keywords, $facebook_description, $linkedin_description, $instagram_description, $twitter_description) {
                $locales = array_filter(SettingsManager::read('Translations', []));

                $translations = [];
                foreach ($locales as $locale => $enabled) {
                    $translationResult = $this->translateClient->translateBatch(
                        [
                            $name,
                            $description,
                            $meta_title,
                            $meta_description,
                            $meta_keywords,
                            $facebook_description,
                            $linkedin_description,
                            $instagram_description,
                            $twitter_description,
                        ],
                        [
                            'source' => 'en',
                            'target' => $locale,
                        ],
                    );
                    $translations[$locale]['name'] = $translationResult[0]['text'];
                    $translations[$locale]['description'] = $translationResult[1]['text'];
                    $translations[$locale]['meta_title'] = $translationResult[2]['text'];
                    $translations[$locale]['meta_description'] = $translationResult[3]['text'];
                    $translations[$locale]['meta_keywords'] = $translationResult[4]['text'];
                    $translations[$locale]['facebook_description'] = $translationResult[5]['text'];
                    $translations[$locale]['linkedin_description'] = $translationResult[6]['text'];
                    $translations[$locale]['instagram_description'] = $translationResult[7]['text'];
                    $translations[$locale]['twitter_description'] = $translationResult[8]['text'];
                }

                return $translations;
            },
        );
    }

    /**
     * Preprocesses content to identify and store code blocks, video placeholders, and gallery placeholders before translation.
     *
     * This method extracts code blocks (markdown, pre, code tags), video placeholders, and image gallery
     * placeholders from the content and replaces them with unique placeholders. The original content is
     * stored in the $preservedBlocks property for later restoration.
     *
     * @param string $content The content containing blocks to be processed
     * @return string The content with preserved blocks replaced by placeholders
     */
    private function preprocessContent(string $content): string
    {
        $this->preservedBlocks = [];

        // Process code blocks, video placeholders, and gallery placeholders
        $patterns = [
            // Code blocks pattern
            '/(```[a-z]*\n[\s\S]*?\n```)|(<pre[\s\S]*?<\/pre>)|(<code[\s\S]*?<\/code>)/m',
            // YouTube video placeholder pattern
            '/\[youtube:[a-zA-Z0-9_-]+:\d+:\d+:[^\]]*\]/m',
            // Image gallery placeholder pattern
            '/\[gallery:[a-f0-9-]+:[^:]*:[^\]]*\]/m',
        ];

        foreach ($patterns as $pattern) {
            $content = preg_replace_callback(
                $pattern,
                function ($matches) {
                    // Use HTML comment syntax for placeholders to prevent translation
                    $placeholder = sprintf('<!--PRESERVED_BLOCK_%d-->', count($this->preservedBlocks));
                    $this->preservedBlocks[$placeholder] = $matches[0];

                    return $placeholder;
                },
                $content,
            );
        }

        return $content;
    }

    /**
     * Restores previously stored content blocks back into the content.
     *
     * This method replaces the placeholder tokens with their original content
     * that was stored during preprocessing. This ensures code blocks and video
     * placeholders maintain their original formatting and content after translation.
     *
     * @param string $content The content containing placeholders
     * @return string The content with original blocks restored
     */
    private function postprocessContent(string $content): string
    {
        // Restore all preserved blocks
        foreach ($this->preservedBlocks as $placeholder => $originalContent) {
            $content = str_replace($placeholder, $originalContent, $content);
        }

        return $content;
    }

    /**
     * Executes a translation operation with comprehensive metrics recording.
     *
     * @param string $taskType The type of task (e.g., 'google_translate_article')
     * @param array $contentToTranslate The content being translated for cost calculation
     * @param callable $operation The translation operation to execute
     * @return mixed The result of the translation operation
     * @throws \App\Service\Api\Google\TranslationException If the operation fails
     */
    private function executeWithMetrics(string $taskType, array $contentToTranslate, callable $operation): mixed
    {
        $startTime = microtime(true);
        $success = false;
        $errorMessage = null;
        $characterCount = $this->metricsService->countCharacters($contentToTranslate);
        $cost = $this->metricsService->calculateGoogleTranslateCost($characterCount);

        try {
            // Check if daily cost limit would be exceeded
            if ($this->metricsService->isDailyCostLimitReached()) {
                throw new TranslationException('Daily cost limit reached for AI services');
            }

            // Enforce hourly rate limit before making the API call
            $rateLimitService = new RateLimitService();
            if (!$rateLimitService->enforceLimit('google')) {
                throw new TranslationException('Hourly rate limit exceeded for Google API');
            }

            // Check cost alert before making the API call
            $currentCost = $this->metricsService->getDailyCost();
            $this->metricsService->checkCostAlert($currentCost, $cost);

            $result = $operation();
            $success = true;

            return $result;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            throw new TranslationException('Translation failed: ' . $e->getMessage(), 0, $e);
        } finally {
            // Record metrics
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                $taskType,
                $executionTime,
                $success,
                $errorMessage,
                null, // Google Translate doesn't provide token counts
                $success ? $cost : null, // Only record cost if successful
                'Google Cloud Translate',
            );
        }
    }
}
