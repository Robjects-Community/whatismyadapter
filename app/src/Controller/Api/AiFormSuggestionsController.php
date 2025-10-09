<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\ProductFormFieldService;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * API Controller for AI Form Suggestions
 *
 * Handles API requests for AI-powered form field suggestions
 * Used by the admin interface to test AI suggestions
 */
class AiFormSuggestionsController extends AppController
{
    /**
     * Handle AI suggestion requests for product form fields
     *
     * @return \Cake\Http\Response JSON response with AI suggestions
     */
    public function index(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            // Get request data
            $fieldName = $this->request->getData('field_name');
            $existingData = $this->request->getData('existing_data', []);

            if (empty($fieldName)) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Field name is required',
                    ]));
            }

            // Load ProductFormFields table to find field by name
            $ProductFormFields = TableRegistry::getTableLocator()->get('ProductFormFields');
            $field = $ProductFormFields->find()
                ->where([
                    'field_name' => $fieldName,
                    'is_active' => true,
                    'ai_enabled' => true,
                ])
                ->first();

            if (!$field) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'suggestions' => [],
                        'confidence_level' => 0,
                        'reasoning' => 'AI suggestions not available for this field',
                    ]));
            }

            // Initialize service with null AI provider (will return empty suggestions)
            $productFormFieldService = new ProductFormFieldService(null);

            // Get AI suggestions using field name
            $result = $productFormFieldService->getAiSuggestions($fieldName, $existingData);

            // Extract suggestions from service response
            $suggestions = $result['suggestions'] ?? [];
            $confidence = $result['confidence'] ?? 0;
            $reasoning = $result['reasoning'] ?? $this->generateReasoning($fieldName, $existingData);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'suggestions' => $suggestions,
                    'confidence_level' => $confidence,
                    'reasoning' => $reasoning,
                ]));
        } catch (Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Internal server error: ' . $e->getMessage(),
                ]));
        }
    }

    /**
     * Calculate confidence level based on available data
     */
    private function calculateConfidenceLevel(array $existingData, string $fieldName): int
    {
        $baseConfidence = 50;
        $bonusPerField = 10;

        // Higher confidence with more existing data
        $dataCount = count(array_filter($existingData, function ($value) {
            return !empty($value) && $value !== '';
        }));

        $confidence = min(95, $baseConfidence + ($dataCount * $bonusPerField));

        // Specific field adjustments
        switch ($fieldName) {
            case 'manufacturer':
            case 'model_number':
                return max($confidence, 70); // Higher confidence for these fields
            case 'price':
            case 'description':
                return max($confidence, 60);
            default:
                return $confidence;
        }
    }

    /**
     * Generate reasoning text for the suggestion
     */
    private function generateReasoning(string $fieldName, array $existingData): string
    {
        $reasons = [];

        if (!empty($existingData['title'])) {
            $reasons[] = "based on the product title '{$existingData['title']}'";
        }

        if (!empty($existingData['manufacturer'])) {
            $reasons[] = "considering the manufacturer '{$existingData['manufacturer']}'";
        }

        if (!empty($existingData['description'])) {
            $reasons[] = 'analyzing the product description';
        }

        $context = empty($reasons) ? 'the available product information' : implode(' and ', $reasons);

        switch ($fieldName) {
            case 'manufacturer':
                return "Suggested manufacturer {$context}.";
            case 'model_number':
                return "Suggested model number {$context}.";
            case 'price':
                return "Estimated price range {$context}.";
            case 'description':
                return "Generated description {$context}.";
            case 'alt_text':
                return "Generated alt text {$context}.";
            default:
                return "Suggested value {$context}.";
        }
    }
}
