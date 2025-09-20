<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\ProductsReliabilityFieldsTable;
use App\Model\Table\ProductsReliabilityLogsTable;
use App\Model\Table\ProductsReliabilityTable;
use App\Model\Table\ProductsTable;
use App\Service\Ai\AiProviderInterface;
use App\Service\Ai\NullAiProvider;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use DateTime;
use Exception;

/**
 * ReliabilityService - Core service for product reliability scoring and persistence
 *
 * Handles scoring calculations, field weighting, checksum generation,
 * and integration with AI providers for suggestions.
 */
class ReliabilityService
{
    private ProductsReliabilityTable $reliabilityTable;
    private ProductsReliabilityFieldsTable $fieldsTable;
    private ProductsReliabilityLogsTable $logsTable;
    private ProductsTable $productsTable;
    private AiProviderInterface $aiProvider;

    /**
     * Constructor - Initialize reliability service with required table instances
     */
    public function __construct()
    {
        $this->reliabilityTable = TableRegistry::getTableLocator()->get('ProductsReliability');
        $this->fieldsTable = TableRegistry::getTableLocator()->get('ProductsReliabilityFields');
        $this->logsTable = TableRegistry::getTableLocator()->get('ProductsReliabilityLogs');
        $this->productsTable = TableRegistry::getTableLocator()->get('Products');

        // Initialize AI provider based on configuration
        $this->initializeAiProvider();
    }

    /**
     * Compute provisional reliability score without persisting to database
     * Used for real-time scoring in forms
     *
     * @param string $model Model name (e.g., 'Products')
     * @param array $payload Product data payload
     * @param array $options Scoring options
     * @return array Scoring results with field breakdown
     */
    public function computeProvisionalScore(string $model, array $payload, array $options = []): array
    {
        // Get field weights from ProductsTable Reliability behavior configuration
        $fieldWeights = $this->getFieldWeights($model);

        // Calculate scores for each field
        $fieldScores = [];
        $totalWeightedScore = 0;
        $totalWeight = 0;
        $completedFields = 0;
        $totalFields = count($fieldWeights);

        foreach ($fieldWeights as $field => $weight) {
            $score = $this->calculateFieldScore($field, $payload[$field] ?? null);
            $contribution = $score * $weight;
            $totalWeightedScore += $contribution;
            $totalWeight += $weight;

            if ($score > 0) {
                $completedFields++;
            }

            $fieldScores[$field] = [
                'score' => round($score, 3),
                'weight' => round($weight, 3),
                'contribution' => round($contribution, 3),
                'max_score' => 1.00,
                'notes' => $this->getFieldScoreRationale($field, $payload[$field] ?? null, $score),
            ];
        }

        // Calculate final scores
        $totalScore = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        $completenessPercent = $totalFields > 0 ? $completedFields / $totalFields * 100 : 0;

        // Get AI suggestions
        $aiContext = [
            'field_weights' => $fieldWeights,
            'field_scores' => $fieldScores,
            'total_score' => $totalScore,
            'completeness_percent' => $completenessPercent,
        ];

        $aiSuggestions = $this->aiProvider->getSuggestions($payload, $aiContext);

        // Determine UI severity based on score
        $severity = match (true) {
            $totalScore >= 0.80 => 'success',
            $totalScore >= 0.60 => 'warning',
            default => 'info'
        };

        return [
            'total_score' => round($totalScore, 3),
            'completeness_percent' => round($completenessPercent, 2),
            'field_scores' => $fieldScores,
            'version' => 'v2.0',
            'source' => $aiSuggestions['source'] ?? 'system',
            'suggestions' => $aiSuggestions['suggestions'] ?? [],
            'reasoning' => $aiSuggestions['reasoning']
                ?? 'Score calculated using field weights and completion status',
            'ui' => [
                'severity' => $severity,
                'field_importance' => $this->calculateFieldImportance($fieldWeights, $fieldScores),
            ],
        ];
    }

    /**
     * Persist final reliability score to database with logging
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $entityId Entity ID
     * @param array $scoreData Score data from computeProvisionalScore
     * @param array $context Context information (actor, source, etc.)
     * @return bool Success status
     */
    public function persistFinalScore(string $model, string $entityId, array $scoreData, array $context = []): bool
    {
        $connection = $this->reliabilityTable->getConnection();

        try {
            $connection->transactional(function () use ($model, $entityId, $scoreData, $context): void {
                $now = new DateTime();

                // Get existing reliability record
                $existing = $this->reliabilityTable->findSummaryFor($model, $entityId);

                // Prepare summary data
                $summaryData = [
                    'model' => $model,
                    'foreign_key' => $entityId,
                    'total_score' => $scoreData['total_score'],
                    'completeness_percent' => $scoreData['completeness_percent'],
                    'field_scores_json' => json_encode(
                        $scoreData['field_scores'],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                    'scoring_version' => $scoreData['version'],
                    'last_source' => $context['source'] ?? 'system',
                    'last_calculated' => $now,
                    'updated_by_user_id' => $context['actor_user_id'] ?? null,
                    'updated_by_service' => $context['actor_service'] ?? 'reliability-service',
                    'modified' => $now,
                ];

                if ($existing) {
                    $summaryData['id'] = $existing->id;
                    $summary = $this->reliabilityTable->patchEntity($existing, $summaryData);
                } else {
                    $summaryData['id'] = Text::uuid();
                    $summaryData['created'] = $now;
                    $summary = $this->reliabilityTable->newEntity($summaryData);
                }

                $this->reliabilityTable->saveOrFail($summary);

                // Update individual field scores
                $this->updateFieldScores($model, $entityId, $scoreData['field_scores'], $now);

                // Create log entry with checksum
                $this->createLogEntry($model, $entityId, $existing, $scoreData, $context, $now);

                // Update legacy products.reliability_score field if needed
                $this->updateLegacyReliabilityScore($model, $entityId, $scoreData['total_score']);
            });

            return true;
        } catch (Exception $e) {
            // Log error but don't throw to avoid breaking form submissions
            error_log('ReliabilityService::persistFinalScore failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Compute checksum for log entry verification
     *
     * @param array $payload Log payload data
     * @return string SHA256 checksum
     */
    public function computeChecksum(array $payload): string
    {
        // Ensure consistent key ordering
        ksort($payload);

        // Canonicalize JSON representation
        $jsonString = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $jsonString);
    }

    /**
     * Calculate field importance for UI highlighting
     * Higher importance = higher weight + lower average score across corpus
     *
     * @param array $fieldWeights Configured field weights
     * @param array $fieldScores Current field scores
     * @return array Top 3 fields to highlight
     */
    private function calculateFieldImportance(array $fieldWeights, array $fieldScores): array
    {
        // Get corpus statistics for dynamic importance
        $stats = $this->fieldsTable->getFieldStats('Products');

        $importance = [];
        foreach ($fieldWeights as $field => $weight) {
            $avgScore = $stats[$field]['avg_score'] ?? 0.5;
            $currentScore = $fieldScores[$field]['score'] ?? 0;

            // Emphasize fields that are both important and commonly weak
            $normalizedImportance = $weight * (1 - $avgScore) * (1 - $currentScore);
            $importance[$field] = $normalizedImportance;
        }

        // Return top 3 fields
        arsort($importance);

        return array_slice(array_keys($importance), 0, 3);
    }

    /**
     * Get field weights from ProductsTable configuration
     *
     * @param string $model Model name
     * @return array Field weights
     */
    private function getFieldWeights(string $model): array
    {
        if ($model === 'Products') {
            // Access the Reliability behavior configuration from ProductsTable
            $behavior = $this->productsTable->getBehavior('Reliability');

            return $behavior->getConfig('fields') ?? [];
        }

        return [];
    }

    /**
     * Calculate score for individual field
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return float Score between 0.0 and 1.0
     */
    private function calculateFieldScore(string $field, mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return match ($field) {
            'technical_specifications' => $this->scoreJsonField($value),
            'testing_standard', 'certifying_organization' => $this->scoreVerificationField($value),
            'numeric_rating', 'performance_rating' => $this->scoreNumericField($value),
            'is_certified' => $this->scoreBooleanField($value),
            'title', 'manufacturer' => $this->scoreTextualField($value, 3, 100),
            'description' => $this->scoreTextualField($value, 10, 500),
            'model_number' => $this->scoreTextualField($value, 2, 50),
            'price' => $this->scorePriceField($value),
            'currency' => $this->scoreCurrencyField($value),
            'image', 'alt_text' => $this->scoreMediaField($value),
            default => $this->scoreGenericField($value)
        };
    }

    /**
     * Score JSON field (technical specifications)
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreJsonField(mixed $value): float
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Higher score for more comprehensive JSON
                $keyCount = count($decoded);

                return min(1.0, $keyCount / 5); // Perfect score at 5+ keys
            }
        }

        return 0.0;
    }

    /**
     * Score verification fields (testing_standard, certifying_organization)
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreVerificationField(mixed $value): float
    {
        $value = trim((string)$value);
        if (strlen($value) < 2) {
            return 0.0;
        }

        // Check for common standards/organizations
        $knownPatterns = [
            'ANSI', 'IEEE', 'ISO', 'IEC', 'FCC', 'UL', 'CE', 'ETL',
            'NEMA', 'TIA', 'EIA', 'JEDEC', 'USB-IF',
        ];

        foreach ($knownPatterns as $pattern) {
            if (stripos($value, $pattern) !== false) {
                return 1.0; // Perfect score for recognized standards
            }
        }

        return strlen($value) >= 3 ? 0.8 : 0.4; // Good score for other text
    }

    /**
     * Score numeric rating fields
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreNumericField(mixed $value): float
    {
        if (is_numeric($value)) {
            $num = (float)$value;

            return $num > 0 ? 1.0 : 0.0;
        }

        return 0.0;
    }

    /**
     * Score boolean field (is_certified)
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreBooleanField(mixed $value): float
    {
        return (bool)$value ? 1.0 : 0.5; // Partial credit for false
    }

    /**
     * Score textual field with length considerations
     *
     * @param mixed $value The field value to score
     * @param int $minLength Minimum acceptable length
     * @param int $idealLength Ideal length for full score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreTextualField(mixed $value, int $minLength, int $idealLength): float
    {
        $value = trim((string)$value);
        $length = strlen($value);

        if ($length < $minLength) {
            return 0.0;
        }
        if ($length >= $idealLength) {
            return 1.0;
        }

        // Linear interpolation between min and ideal length
        return ($length - $minLength) / ($idealLength - $minLength);
    }

    /**
     * Score price field
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scorePriceField(mixed $value): float
    {
        return is_numeric($value) && (float)$value > 0 ? 1.0 : 0.0;
    }

    /**
     * Score currency field
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreCurrencyField(mixed $value): float
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD'];

        return in_array(strtoupper((string)$value), $validCurrencies) ? 1.0 : 0.5;
    }

    /**
     * Score media field (image, alt_text)
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreMediaField(mixed $value): float
    {
        $value = trim((string)$value);

        return strlen($value) > 0 ? 1.0 : 0.0;
    }

    /**
     * Score generic field
     *
     * @param mixed $value The field value to score
     * @return float Score between 0.0 and 1.0
     */
    private function scoreGenericField(mixed $value): float
    {
        return !empty($value) && trim((string)$value) !== '' ? 1.0 : 0.0;
    }

    /**
     * Get field score rationale for UI display
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param float $score Calculated score
     * @return string Human-readable rationale
     */
    private function getFieldScoreRationale(string $field, mixed $value, float $score): string
    {
        if ($score === 0.0) {
            return 'Field is empty or invalid';
        }

        if ($score === 1.0) {
            return match ($field) {
                'technical_specifications' => 'Comprehensive JSON specification provided',
                'testing_standard', 'certifying_organization' => 'Recognized standard/organization',
                default => 'Complete and valid'
            };
        }

        return 'Partially complete - could be improved';
    }

    /**
     * Update individual field scores in database
     */
    private function updateFieldScores(string $model, string $entityId, array $fieldScores, DateTime $now): void
    {
        // Delete existing field scores
        $this->fieldsTable->deleteAll([
            'model' => $model,
            'foreign_key' => $entityId,
        ]);

        // Insert new field scores
        $entities = [];
        foreach ($fieldScores as $field => $data) {
            $entities[] = $this->fieldsTable->newEntity([
                'model' => $model,
                'foreign_key' => $entityId,
                'field' => $field,
                'score' => $data['score'],
                'weight' => $data['weight'],
                'max_score' => $data['max_score'],
                'notes' => $data['notes'],
                'created' => $now,
                'modified' => $now,
            ]);
        }

        if (!empty($entities)) {
            $this->fieldsTable->saveManyOrFail($entities);
        }
    }

    /**
     * Create log entry with checksum
     *
     * @param string $model Model name
     * @param string $entityId Entity ID
     * @param mixed $existing Existing reliability record (or null)
     * @param array $scoreData Score data
     * @param array $context Context information
     * @param \DateTime $now Current timestamp
     * @return void
     */
    private function createLogEntry(
        string $model,
        string $entityId,
        mixed $existing,
        array $scoreData,
        array $context,
        DateTime $now,
    ): void {
        $logPayload = [
            'model' => $model,
            'foreign_key' => $entityId,
            'from_total_score' => $existing?->total_score,
            'to_total_score' => $scoreData['total_score'],
            'from_field_scores_json' => $existing?->field_scores_json
                ? json_decode($existing->field_scores_json, true)
                : null,
            'to_field_scores_json' => $scoreData['field_scores'],
            'source' => $context['source'] ?? 'system',
            'actor_user_id' => $context['actor_user_id'] ?? null,
            'actor_service' => $context['actor_service'] ?? 'reliability-service',
            'created' => $now->format('c'),
        ];

        $checksum = $this->computeChecksum($logPayload);

        $logEntity = $this->logsTable->newEntity([
            'id' => Text::uuid(),
            'model' => $model,
            'foreign_key' => $entityId,
            'from_total_score' => $existing?->total_score,
            'to_total_score' => $scoreData['total_score'],
            'from_field_scores_json' => $existing?->field_scores_json,
            'to_field_scores_json' => json_encode(
                $scoreData['field_scores'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ),
            'source' => $context['source'] ?? 'system',
            'actor_user_id' => $context['actor_user_id'] ?? null,
            'actor_service' => $context['actor_service'] ?? 'reliability-service',
            'message' => $context['message'] ?? 'Score updated via ReliabilityService',
            'checksum_sha256' => $checksum,
            'created' => $now,
        ]);

        $this->logsTable->saveOrFail($logEntity);
    }

    /**
     * Update legacy products.reliability_score field for backward compatibility
     */
    private function updateLegacyReliabilityScore(string $model, string $entityId, float $totalScore): void
    {
        if ($model === 'Products') {
            $this->productsTable->updateAll(
                ['reliability_score' => $totalScore, 'modified' => new DateTime()],
                ['id' => $entityId],
            );
        }
    }

    /**
     * Initialize AI provider based on configuration
     */
    private function initializeAiProvider(): void
    {
        $config = Configure::read('Reliability', []);
        $providerType = $config['aiProvider'] ?? 'null';

        switch ($providerType) {
            case 'openai':
                // TODO: Implement OpenAI provider when needed
                // For now, fall back to null provider
            case 'null':
            default:
                $this->aiProvider = new NullAiProvider();
                break;
        }
    }
}
