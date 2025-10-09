<?php
declare(strict_types=1);

namespace App\Service\Ai;

/**
 * Interface for AI providers that generate product reliability suggestions
 */
interface AiProviderInterface
{
    /**
     * Get AI-powered suggestions for improving product reliability
     *
     * @param array $productData Current product data payload
     * @param array $context Additional context (field_weights, current_scores, etc.)
     * @return array Array with keys: suggestions[], reasoning, confidence_level
     */
    public function getSuggestions(array $productData, array $context = []): array;
}
