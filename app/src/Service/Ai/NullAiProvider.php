<?php
declare(strict_types=1);

namespace App\Service\Ai;

/**
 * Null AI provider that returns deterministic heuristic suggestions
 * Fast and safe fallback when no AI provider is configured
 */
class NullAiProvider implements AiProviderInterface
{
    /**
     * Returns heuristic suggestions based on common field patterns
     *
     * @param array $productData Product data payload
     * @param array $context Context including field weights and scores
     * @return array Structured response with suggestions and reasoning
     */
    public function getSuggestions(array $productData, array $context = []): array
    {
        $suggestions = [];
        $reasoning = 'Using deterministic heuristics for suggestions';

        // Analyze missing high-weight fields
        $fieldWeights = $context['field_weights'] ?? [];
        $fieldScores = $context['field_scores'] ?? [];

        // Sort fields by weight (descending) to prioritize important ones
        arsort($fieldWeights);

        foreach ($fieldWeights as $field => $weight) {
            $currentScore = $fieldScores[$field]['score'] ?? 0;

            if ($weight >= 0.15 && $currentScore < 0.5) {
                $suggestions[] = $this->getFieldSuggestion($field, $productData);
            }

            // Only show top 3 suggestions to avoid overwhelming users
            if (count($suggestions) >= 3) {
                break;
            }
        }

        // Default suggestions if none found
        if (empty($suggestions) && empty($productData['title'])) {
            $suggestions[] = 'Add a clear, descriptive product title to improve searchability.';
        }

        return [
            'suggestions' => $suggestions,
            'reasoning' => $reasoning,
            'confidence_level' => 'high', // High confidence in heuristic rules
            'source' => 'heuristic',
        ];
    }

    /**
     * Get field-specific suggestion
     *
     * @param string $field Field name
     * @param array $productData Current product data
     * @return string Suggestion text
     */
    private function getFieldSuggestion(string $field, array $productData): string
    {
        return match ($field) {
            'technical_specifications' => 'Add technical specifications in JSON format to increase reliability score significantly.',
            'testing_standard' => 'Specify which testing standard was used (e.g., ANSI, IEEE, ISO) to verify product performance.',
            'certifying_organization' => 'Include the certifying organization name to establish trust and verification.',
            'numeric_rating' => 'Add a numeric performance rating to help users compare products.',
            'performance_rating' => 'Include a performance rating to indicate product quality level.',
            'manufacturer' => 'Add manufacturer information to establish product origin and support.',
            'model_number' => 'Include the exact model number for precise product identification.',
            'description' => 'Provide a detailed description explaining product features and benefits.',
            'price' => 'Add pricing information to help users make informed decisions.',
            'is_certified' => 'Indicate whether this product has official certifications.',
            default => "Complete the '{$field}' field to improve your product's reliability score."
        };
    }
}
