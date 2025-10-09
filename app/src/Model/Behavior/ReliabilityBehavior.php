<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Exception;

/**
 * Reliability Behavior
 *
 * Handles scoring, completeness calculation, and audit logging for the
 * polymorphic reliability system.
 */
class ReliabilityBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'fields' => [
            // Critical verification fields (high weight, require external validation)
            'technical_specifications' => 0.25, // JSON specs are critical
            'testing_standard' => 0.20, // Must have testing standard
            'certifying_organization' => 0.15, // Must have certifier
            'numeric_rating' => 0.10, // Must have performance rating

            // Basic product information (lower weight without verification)
            'title' => 0.08,
            'description' => 0.08,
            'manufacturer' => 0.05,
            'model_number' => 0.03,
            'price' => 0.03,
            'currency' => 0.01,
            'image' => 0.01,
            'alt_text' => 0.01,
        ],
        'scoring_version' => 'v2.0', // Updated version with verification focus
        'normalize' => true, // Normalize to 0.00-1.00 range
        'verification_required' => true, // Products need verification to score well
    ];

    /**
     * Cached configuration from reliability.php
     *
     * @var array|null
     */
    protected ?array $_reliabilityConfig = null;

    /**
     * Initialize method
     *
     * @param array $config Configuration array
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Load reliability configuration
        $this->_reliabilityConfig = Configure::read('Reliability', []);

        // Merge model-specific config if available
        $modelName = $this->_table->getAlias();
        if (isset($this->_reliabilityConfig[$modelName])) {
            $this->setConfig($this->_reliabilityConfig[$modelName]);
        }
    }

    /**
     * Score individual fields based on content and quality
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @return array Field scores with metadata
     */
    public function scoreFields(EntityInterface $entity): array
    {
        $modelName = $this->_table->getAlias();
        $fieldWeights = $this->getConfig('fields', []);
        $thresholds = $this->_getThresholds($modelName);
        $fieldScores = [];

        foreach ($fieldWeights as $field => $weight) {
            $fieldScores[$field] = $this->_scoreField($entity, $field, $weight, $thresholds);
        }

        return $fieldScores;
    }

    /**
     * Score a single field
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param string $field Field name
     * @param float $weight Field weight
     * @param array $thresholds Scoring thresholds
     * @return array Field score data
     */
    protected function _scoreField(EntityInterface $entity, string $field, float $weight, array $thresholds): array
    {
        $value = $entity->get($field);
        $score = 0.0;
        $notes = '';
        $maxScore = 1.0;

        switch ($field) {
            case 'title':
                if (!empty($value) && is_string($value)) {
                    $score = 1.0;
                    $notes = 'Title present and valid';
                } else {
                    $notes = 'Title missing or invalid';
                }
                break;

            case 'description':
                if (empty($value)) {
                    $notes = 'Description missing';
                } else {
                    $length = strlen(strip_tags($value));
                    $minLength = $thresholds['description']['min_length'] ?? 20;
                    $goodLength = $thresholds['description']['good_length'] ?? 100;
                    $excellentLength = $thresholds['description']['excellent_length'] ?? 300;

                    if ($length < $minLength) {
                        $score = 0.0;
                        $notes = "Description too short ({$length} chars, min {$minLength})";
                    } elseif ($length < $goodLength) {
                        $score = 0.5 + (($length - $minLength) / ($goodLength - $minLength)) * 0.25;
                        $notes = "Description adequate ({$length} chars)";
                    } elseif ($length < $excellentLength) {
                        $score = 0.75 + (($length - $goodLength) / ($excellentLength - $goodLength)) * 0.25;
                        $notes = "Description good ({$length} chars)";
                    } else {
                        $score = 1.0;
                        $notes = "Description excellent ({$length} chars)";
                    }
                }
                break;

            case 'manufacturer':
                if (!empty($value) && is_string($value)) {
                    $score = 1.0;
                    $notes = 'Manufacturer specified';
                } else {
                    $notes = 'Manufacturer missing';
                }
                break;

            case 'model_number':
                if (!empty($value) && is_string($value)) {
                    $score = 1.0;
                    $notes = 'Model number specified';
                } else {
                    $notes = 'Model number missing';
                }
                break;

            case 'price':
                $minValue = $thresholds['price']['min_value'] ?? 0.01;
                if (is_numeric($value) && $value >= $minValue) {
                    $score = 1.0;
                    $notes = 'Valid price specified';
                } else {
                    $notes = empty($value) ? 'Price missing' : 'Invalid price value';
                }
                break;

            case 'currency':
                $validCurrencies = $this->_getValidCurrencies();
                if (!empty($value) && in_array(strtoupper($value), $validCurrencies)) {
                    $score = 1.0;
                    $notes = 'Valid currency code';
                } else {
                    $notes = empty($value) ? 'Currency missing' : 'Invalid currency code';
                }
                break;

            case 'image':
                if (!empty($value)) {
                    if ($this->_isValidImagePath($value)) {
                        $score = 1.0;
                        $notes = 'Valid image URL/path';
                    } else {
                        $score = 0.5;
                        $notes = 'Image path present but format may be invalid';
                    }
                } else {
                    $notes = 'Image missing';
                }
                break;

            case 'alt_text':
                if (!empty($value)) {
                    $length = strlen($value);
                    $minLength = $thresholds['alt_text']['min_length'] ?? 5;
                    $goodLength = $thresholds['alt_text']['good_length'] ?? 20;

                    if ($length >= $goodLength) {
                        $score = 1.0;
                        $notes = "Alt text descriptive ({$length} chars)";
                    } elseif ($length >= $minLength) {
                        $score = 0.7;
                        $notes = "Alt text adequate ({$length} chars)";
                    } else {
                        $score = 0.3;
                        $notes = "Alt text too short ({$length} chars)";
                    }
                } else {
                    $notes = 'Alt text missing';
                }
                break;

            case 'technical_specifications':
                if (!empty($value)) {
                    // Try to parse as JSON
                    if (is_string($value)) {
                        $jsonData = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData) && count($jsonData) > 0) {
                            $score = 1.0;
                            $notes = 'Valid technical specifications JSON provided';
                        } else {
                            $score = 0.3;
                            $notes = 'Technical specifications present but invalid JSON format';
                        }
                    } else {
                        $score = 0.5;
                        $notes = 'Technical specifications provided but not in JSON format';
                    }
                } else {
                    $score = 0.0;
                    $notes = 'CRITICAL: Technical specifications missing - product cannot be verified';
                }
                break;

            case 'testing_standard':
                if (!empty($value) && is_string($value)) {
                    // Check if it looks like a real testing standard
                    if (
                        preg_match('/^[A-Z]{2,8}[-\s]?\d+/', $value) ||
                        strpos($value, 'ISO') !== false ||
                        strpos($value, 'ANSI') !== false ||
                        strpos($value, 'IEC') !== false ||
                        strpos($value, 'IEEE') !== false
                    ) {
                        $score = 1.0;
                        $notes = 'Valid testing standard format detected';
                    } else {
                        $score = 0.2;
                        $notes = 'Testing standard present but format not recognized';
                    }
                } else {
                    $score = 0.0;
                    $notes = 'CRITICAL: Testing standard missing - product authenticity questionable';
                }
                break;

            case 'certifying_organization':
                if (!empty($value) && is_string($value)) {
                    // Check for known certifying organizations
                    $validOrgs = ['UL', 'FCC', 'CE', 'ETL', 'CSA', 'TUV', 'SGS', 'DNV', 'BV'];
                    $foundValid = false;
                    foreach ($validOrgs as $org) {
                        if (stripos($value, $org) !== false) {
                            $foundValid = true;
                            break;
                        }
                    }

                    if ($foundValid) {
                        $score = 1.0;
                        $notes = 'Recognized certifying organization detected';
                    } else {
                        $score = 0.3;
                        $notes = 'Certifying organization provided but not recognized';
                    }
                } else {
                    $score = 0.0;
                    $notes = 'CRITICAL: Certifying organization missing - no third-party verification';
                }
                break;

            case 'numeric_rating':
                if (is_numeric($value) && $value > 0) {
                    // Check if the rating is in a reasonable range
                    if ($value >= 0.1 && $value <= 100) {
                        $score = 1.0;
                        $notes = 'Valid numeric performance rating provided';
                    } else {
                        $score = 0.5;
                        $notes = 'Numeric rating present but value seems unrealistic';
                    }
                } else {
                    $score = 0.0;
                    $notes = 'CRITICAL: Numeric performance rating missing - no quantified performance data';
                }
                break;

            default:
                // Generic field scoring for unknown fields
                if (!empty($value)) {
                    $score = 1.0;
                    $notes = 'Field has value';
                } else {
                    $notes = 'Field is empty';
                }
        }

        return [
            'score' => round($score, 3),
            'weight' => $weight,
            'max_score' => $maxScore,
            'notes' => $notes,
        ];
    }

    /**
     * Analyze completeness of an entity
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param array $fields Field configuration
     * @return float Completeness percentage (0-100)
     */
    public function analyzeCompleteness(EntityInterface $entity, array $fields): float
    {
        $modelName = $this->_table->getAlias();
        $completenessMethod = $this->_reliabilityConfig[$modelName]['completeness_method'] ?? 'binary';

        $totalFields = count($fields);
        if ($totalFields === 0) {
            return 0.0;
        }

        $fieldScores = $this->scoreFields($entity);
        $completeness = 0.0;

        if ($completenessMethod === 'weighted') {
            // Use actual scores weighted by field importance
            $weightedSum = 0.0;
            $totalWeight = 0.0;

            foreach ($fieldScores as $fieldData) {
                $weightedSum += $fieldData['score'] * $fieldData['weight'];
                $totalWeight += $fieldData['weight'];
            }

            $completeness = $totalWeight > 0 ? $weightedSum / $totalWeight * 100 : 0.0;
        } else {
            // Binary method - field is complete if score > 0
            $completeFields = 0;
            foreach ($fieldScores as $fieldData) {
                if ($fieldData['score'] > 0.0) {
                    $completeFields++;
                }
            }
            $completeness = $completeFields / $totalFields * 100;
        }

        return round($completeness, 2);
    }

    /**
     * Accumulate individual field scores into total score
     *
     * @param array $fieldScores Field scores array
     * @return array Summary with total_score and completeness_percent
     */
    public function accumulateScores(array $fieldScores): array
    {
        $totalScore = 0.0;
        $totalWeight = 0.0;
        $completeFields = 0;
        $totalFields = count($fieldScores);

        foreach ($fieldScores as $fieldData) {
            $weightedScore = $fieldData['score'] * $fieldData['weight'];
            $totalScore += $weightedScore;
            $totalWeight += $fieldData['weight'];

            if ($fieldData['score'] > 0.0) {
                $completeFields++;
            }
        }

        // Normalize if configured
        if ($this->getConfig('normalize', true) && $totalWeight > 0) {
            $totalScore = min(1.0, $totalScore); // Cap at 1.0
        }

        $completenessPercent = $totalFields > 0 ? $completeFields / $totalFields * 100 : 0.0;

        return [
            'total_score' => round($totalScore, 3),
            'completeness_percent' => round($completenessPercent, 2),
        ];
    }

    /**
     * Compute SHA256 checksum for audit log verification
     *
     * @param array $payload Log payload with ordered keys
     * @return string SHA256 checksum (64 characters)
     */
    public function computeChecksum(array $payload): string
    {
        // Ensure consistent key ordering for deterministic checksums
        $orderedPayload = [
            'model' => $payload['model'] ?? '',
            'foreign_key' => $payload['foreign_key'] ?? '',
            'from_total_score' => $payload['from_total_score'],
            'to_total_score' => $payload['to_total_score'] ?? 0.0,
            'from_field_scores_json' => $this->_canonicalizeJson($payload['from_field_scores_json'] ?? null),
            'to_field_scores_json' => $this->_canonicalizeJson($payload['to_field_scores_json'] ?? null),
            'source' => $payload['source'] ?? 'system',
            'actor_user_id' => $payload['actor_user_id'],
            'actor_service' => $payload['actor_service'],
            'created' => $payload['created'] ?? (new DateTime())->format('c'),
        ];

        // Create canonical JSON representation
        $canonicalJson = json_encode($orderedPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($canonicalJson === false) {
            throw new InternalErrorException('Failed to encode payload for checksum calculation');
        }

        return hash('sha256', $canonicalJson);
    }

    /**
     * Main method to recalculate reliability scores for an entity
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity
     * @param array $context Context information for logging
     * @return bool Success
     */
    public function recalcFor(EntityInterface $entity, array $context = []): bool
    {
        try {
            $modelName = $this->_table->getAlias();
            $entityId = $entity->get($this->_table->getPrimaryKey());

            if (empty($entityId)) {
                Log::error('ReliabilityBehavior: Cannot recalculate for entity without ID', [
                    'model' => $modelName,
                    'entity' => $entity->toArray(),
                ]);

                return false;
            }

            // Get current reliability data
            $reliabilityTable = $this->_table->getTableLocator()->get('ProductsReliability');
            $currentReliability = $reliabilityTable->findSummaryFor($modelName, $entityId);

            // Calculate new scores
            $fieldScores = $this->scoreFields($entity);
            $accumulatedScores = $this->accumulateScores($fieldScores);
            $completeness = $this->analyzeCompleteness($entity, $this->getConfig('fields', []));

            // Prepare summary data
            $summaryData = [
                'total_score' => $accumulatedScores['total_score'],
                'completeness_percent' => $completeness,
                'field_scores_json' => json_encode($fieldScores, JSON_UNESCAPED_UNICODE),
                'scoring_version' => $this->getConfig('scoring_version', 'v1.0'),
                'last_source' => $context['source'] ?? 'system',
                'last_calculated' => new DateTime(),
                'updated_by_user_id' => $context['actor_user_id'] ?? null,
                'updated_by_service' => $context['actor_service'] ?? null,
            ];

            // Save summary and field data, create log entry
            return $this->saveSummary($modelName, $entityId, $summaryData, $fieldScores, $context, $currentReliability);
        } catch (Exception $e) {
            Log::error('ReliabilityBehavior: Recalculation failed', [
                'model' => $modelName ?? 'unknown',
                'entity_id' => $entityId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Save reliability summary, field scores, and create audit log
     *
     * @param string $model Model name
     * @param string $entityId Entity ID
     * @param array $summaryData Summary data
     * @param array $fieldScores Field scores
     * @param array $context Context for logging
     * @param \Cake\Datasource\EntityInterface|null $currentReliability Current reliability record
     * @return bool Success
     */
    protected function saveSummary(
        string $model,
        string $entityId,
        array $summaryData,
        array $fieldScores,
        array $context = [],
        ?EntityInterface $currentReliability = null,
    ): bool {
        $connection = $this->_table->getConnection();

        return $connection->transactional(function () use (
            $model,
            $entityId,
            $summaryData,
            $fieldScores,
            $context,
            $currentReliability,
        ) {
            $reliabilityTable = $this->_table->getTableLocator()->get('ProductsReliability');
            $fieldsTable = $this->_table->getTableLocator()->get('ProductsReliabilityFields');
            $logsTable = $this->_table->getTableLocator()->get('ProductsReliabilityLogs');

            // Prepare log data
            $fromTotalScore = $currentReliability ? $currentReliability->total_score : null;
            $fromFieldScores = $currentReliability ? $currentReliability->field_scores_json : null;

            // Upsert reliability summary
            if ($currentReliability) {
                $reliabilityEntity = $reliabilityTable->patchEntity($currentReliability, $summaryData);
            } else {
                $reliabilityEntity = $reliabilityTable->newEntity(array_merge($summaryData, [
                    'id' => Text::uuid(),
                    'model' => $model,
                    'foreign_key' => $entityId,
                ]));
            }

            if (!$reliabilityTable->save($reliabilityEntity)) {
                Log::error('Failed to save reliability summary', [
                    'model' => $model,
                    'entity_id' => $entityId,
                    'errors' => $reliabilityEntity->getErrors(),
                ]);

                return false;
            }

            // Update field-level scores
            $this->_saveFieldScores($fieldsTable, $model, $entityId, $fieldScores);

            // Create audit log entry
            $logData = [
                'id' => Text::uuid(),
                'model' => $model,
                'foreign_key' => $entityId,
                'from_total_score' => $fromTotalScore,
                'to_total_score' => $summaryData['total_score'],
                'from_field_scores_json' => $fromFieldScores,
                'to_field_scores_json' => $summaryData['field_scores_json'],
                'source' => $context['source'] ?? 'system',
                'actor_user_id' => $context['actor_user_id'] ?? null,
                'actor_service' => $context['actor_service'] ?? null,
                'message' => $context['message'] ?? 'Reliability scores recalculated',
                'created' => new DateTime(),
            ];

            // Compute checksum
            $logData['checksum_sha256'] = $this->computeChecksum($logData);

            $logEntity = $logsTable->newEntity($logData);
            if (!$logsTable->save($logEntity)) {
                Log::error('Failed to save reliability log', [
                    'model' => $model,
                    'entity_id' => $entityId,
                    'errors' => $logEntity->getErrors(),
                ]);

                return false;
            }

            return true;
        });
    }

    /**
     * Save field-level scores
     *
     * @param \Cake\ORM\Table $fieldsTable Fields table instance
     * @param string $model Model name
     * @param string $entityId Entity ID
     * @param array $fieldScores Field scores array
     * @return void
     */
    protected function _saveFieldScores(Table $fieldsTable, string $model, string $entityId, array $fieldScores): void
    {
        // Delete existing field scores for this entity
        $fieldsTable->deleteAll([
            'model' => $model,
            'foreign_key' => $entityId,
        ]);

        // Insert new field scores
        foreach ($fieldScores as $field => $scoreData) {
            $fieldEntity = $fieldsTable->newEntity([
                'model' => $model,
                'foreign_key' => $entityId,
                'field' => $field,
                'score' => $scoreData['score'],
                'weight' => $scoreData['weight'],
                'max_score' => $scoreData['max_score'],
                'notes' => $scoreData['notes'],
            ]);

            if (!$fieldsTable->save($fieldEntity)) {
                Log::warning('Failed to save field score', [
                    'model' => $model,
                    'entity_id' => $entityId,
                    'field' => $field,
                    'errors' => $fieldEntity->getErrors(),
                ]);
            }
        }
    }

    /**
     * Get scoring thresholds for a model
     *
     * @param string $modelName Model name
     * @return array Thresholds configuration
     */
    protected function _getThresholds(string $modelName): array
    {
        return $this->_reliabilityConfig[$modelName]['thresholds'] ?? [];
    }

    /**
     * Get valid currency codes
     *
     * @return array Valid currency codes
     */
    protected function _getValidCurrencies(): array
    {
        $modelName = $this->_table->getAlias();

        return $this->_reliabilityConfig[$modelName]['valid_currencies'] ?? ['USD', 'EUR', 'GBP'];
    }

    /**
     * Validate image path/URL
     *
     * @param string $imagePath Image path or URL
     * @return bool Valid image path
     */
    protected function _isValidImagePath(string $imagePath): bool
    {
        $modelName = $this->_table->getAlias();
        $allowedExts = $this->_reliabilityConfig[$modelName]['image_validation']['allowed_extensions'] ??
                       ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        return in_array($extension, $allowedExts);
    }

    /**
     * Canonicalize JSON for consistent checksums
     *
     * @param mixed $data JSON data
     * @return string Canonical JSON string
     */
    protected function _canonicalizeJson(mixed $data): string
    {
        if ($data === null) {
            return 'null';
        }

        if (is_string($data)) {
            // Already JSON string, decode and re-encode canonically
            $decoded = json_decode($data, true);
            if ($decoded === null) {
                return 'null';
            }
            $data = $decoded;
        }

        // Ensure consistent sorting and formatting
        if (is_array($data)) {
            ksort($data);
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
    }
}
