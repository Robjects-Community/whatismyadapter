<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use InvalidArgumentException;

/**
 * ImageAnalyzer Class
 *
 * This class is responsible for analyzing images using the Anthropic API service.
 * It interacts with the AI prompts table to retrieve prompt data and uses the AnthropicApiService
 * to send requests and parse responses for image analysis.
 */
class ImageAnalyzer extends AbstractAnthropicGenerator
{
    /**
     * Analyzes an image using the Anthropic API.
     *
     * This method performs the following steps:
     * 1. Validates the existence of the image file.
     * 2. Encodes the image file to base64 and determines its MIME type.
     * 3. Retrieves the appropriate prompt data for image analysis.
     * 4. Creates a payload with the image data and prompt information.
     * 5. Sends a request to the Anthropic API and processes the response.
     *
     * @param string $imagePath The path to the image file to be analyzed.
     * @return array The analysis results from the API, containing various aspects of the image analysis.
     * @throws \InvalidArgumentException If the image file is not found or the task prompt data is not found.
     */
    public function analyze(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        $promptData = $this->getPromptData('image_analysis');
        $payload = $this->createImagePayload($promptData, $imageData, $mimeType);

        $result = $this->sendApiRequest($payload);

        return $this->ensureExpectedKeys($result);
    }

    /**
     * Creates a specialized payload for image analysis requests.
     *
     * @param array $promptData The prompt data retrieved from the AI prompts table.
     * @param string $imageData The base64 encoded image data.
     * @param string $mimeType The MIME type of the image.
     * @return array The created payload for the API request.
     */
    private function createImagePayload(array $promptData, string $imageData, string $mimeType): array
    {
        return [
            'model' => $promptData['model'],
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => $promptData['temperature'],
            'system' => $promptData['system_prompt'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Gets the expected keys for the API response.
     *
     * @return array Array of expected response keys.
     */
    protected function getExpectedKeys(): array
    {
        return [
            'name',
            'alt_text',
            'keywords',
        ];
    }

    /**
     * Gets the logger name for this analyzer.
     *
     * @return string The logger name.
     */
    protected function getLoggerName(): string
    {
        return 'Image Analyzer';
    }
}
