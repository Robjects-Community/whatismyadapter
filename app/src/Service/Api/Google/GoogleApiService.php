<?php
declare(strict_types=1);

namespace App\Service\Api\Google;

use App\Utility\SettingsManager;
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
     * Constructor for the GoogleApiService class.
     * Initializes the Google Cloud Translate client with the API key from the settings.
     */
    public function __construct()
    {
        $this->translateClient = new TranslateClient([
            'key' => SettingsManager::read('Google.translateApiKey', env('TRANSLATE_API_KEY')),
        ]);
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

        try {
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
        } catch (Exception $e) {
            throw new TranslationException('Translation failed: ' . $e->getMessage(), 0, $e);
        }
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
}
