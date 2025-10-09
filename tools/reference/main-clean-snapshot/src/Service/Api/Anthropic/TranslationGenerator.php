<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

/**
 * Class TranslationGenerator
 *
 * This class is responsible for generating translations of text using the Anthropic
 * API service. It interacts with the AI prompts table to retrieve prompt data
 * and uses the AnthropicApiService to send requests and parse responses.
 */
class TranslationGenerator extends AbstractAnthropicGenerator
{
    /**
     * Generates translations for a given set of strings from one locale to another.
     *
     * This method prepares a payload with the provided strings and locale information,
     * sends a request to the Anthropic API service for translation, and processes the response
     * to ensure it contains the expected keys.
     *
     * @param array $strings An array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en_US').
     * @param string $localeTo The locale code of the target language (e.g., 'fr_FR').
     * @return array An array containing the translated strings with expected keys.
     */
    public function generateTranslation(array $strings, string $localeFrom, string $localeTo): array
    {
        $promptData = $this->getPromptData('i18n_batch_translation');
        $payload = $this->createPayload($promptData, [
            'strings' => $strings,
            'localeFrom' => $localeFrom,
            'localeTo' => $localeTo,
        ]);

        $timeOut = 45;

        $result = $this->sendApiRequest($payload, $timeOut);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Gets the expected keys for the API response.
     *
     * @return array Array of expected response keys.
     */
    protected function getExpectedKeys(): array
    {
        return ['strings'];
    }

    /**
     * Gets the logger name for this generator.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'Translation Generator';
    }
}
