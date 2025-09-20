<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\ReliabilityService;
use Cake\Http\Response;
use Exception;
use InvalidArgumentException;

/**
 * ReliabilityController - Public API for live scoring and reliability operations
 */
class ReliabilityController extends AppController
{
    private ReliabilityService $reliabilityService;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Allow API access without authentication for now
        // In production, add proper API authentication
        $this->Authentication->allowUnauthenticated(['score', 'verifyChecksum', 'fieldStats']);

        // Set response type to JSON
        $this->viewBuilder()->setClassName('Json');

        // Initialize reliability service
        $this->reliabilityService = new ReliabilityService();
    }

    /**
     * POST /api/reliability/score
     * Calculate provisional reliability score for product data
     *
     * Expected JSON payload:
     * {
     *   "model": "Products",
     *   "data": {
     *     "title": "Sample Product",
     *     "manufacturer": "ACME Corp",
     *     "price": 99.99,
     *     "currency": "USD",
     *     ...
     *   }
     * }
     *
     * Returns scoring results with field breakdown, AI suggestions, and UI hints
     */
    public function score(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            // Parse request data
            $requestData = $this->request->getData();
            $model = $requestData['model'] ?? 'Products';
            $productData = $requestData['data'] ?? [];

            // Validate required fields
            if (empty($productData)) {
                return $this->response
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => 'Missing required field: data',
                        'success' => false,
                    ]));
            }

            // Compute provisional score
            $scoreResult = $this->reliabilityService->computeProvisionalScore($model, $productData);

            // Add request metadata
            $responseData = array_merge($scoreResult, [
                'success' => true,
                'model' => $model,
                'timestamp' => gmdate('c'),
                'request_id' => $this->generateRequestId(),
            ]);

            return $this->response
                ->withStatus(200)
                ->withStringBody(json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (InvalidArgumentException $e) {
            return $this->response
                ->withStatus(400)
                ->withStringBody(json_encode([
                    'error' => $e->getMessage(),
                    'success' => false,
                ]));
        } catch (Exception $e) {
            // Log error but don't expose internal details
            error_log('ReliabilityController::score error: ' . $e->getMessage());

            return $this->response
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'error' => 'Internal server error during score calculation',
                    'success' => false,
                ]));
        }
    }

    /**
     * POST /api/reliability/verify-checksum
     * Verify integrity of a reliability log entry using checksum
     *
     * Expected JSON payload:
     * {
     *   "model": "Products",
     *   "foreign_key": "uuid-here",
     *   "log_id": "log-uuid-here"
     * }
     */
    public function verifyChecksum(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $requestData = $this->request->getData();
            $model = $requestData['model'] ?? '';
            $foreignKey = $requestData['foreign_key'] ?? '';
            $logId = $requestData['log_id'] ?? '';

            if (empty($model) || empty($foreignKey) || empty($logId)) {
                return $this->response
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'error' => 'Missing required fields: model, foreign_key, log_id',
                        'success' => false,
                    ]));
            }

            // Get log entry from database
            $logsTable = $this->fetchTable('ProductsReliabilityLogs');
            $logEntry = $logsTable->find()
                ->where([
                    'id' => $logId,
                    'model' => $model,
                    'foreign_key' => $foreignKey,
                ])
                ->first();

            if (!$logEntry) {
                return $this->response
                    ->withStatus(404)
                    ->withStringBody(json_encode([
                        'error' => 'Log entry not found',
                        'success' => false,
                    ]));
            }

            // Reconstruct payload and verify checksum
            $reconstructedPayload = [
                'model' => $logEntry->model,
                'foreign_key' => $logEntry->foreign_key,
                'from_total_score' => $logEntry->from_total_score,
                'to_total_score' => $logEntry->to_total_score,
                'from_field_scores_json' => $logEntry->from_field_scores_json
                    ? json_decode($logEntry->from_field_scores_json, true)
                    : null,
                'to_field_scores_json' => json_decode($logEntry->to_field_scores_json, true),
                'source' => $logEntry->source,
                'actor_user_id' => $logEntry->actor_user_id,
                'actor_service' => $logEntry->actor_service,
                'created' => $logEntry->created->format('c'),
            ];

            $computedChecksum = $this->reliabilityService->computeChecksum($reconstructedPayload);
            $storedChecksum = $logEntry->checksum_sha256;
            $isValid = hash_equals($storedChecksum, $computedChecksum);

            return $this->response
                ->withStatus(200)
                ->withStringBody(json_encode([
                    'success' => true,
                    'log_id' => $logId,
                    'checksum_valid' => $isValid,
                    'stored_checksum' => $storedChecksum,
                    'computed_checksum' => $computedChecksum,
                    'verified_at' => gmdate('c'),
                ]));
        } catch (Exception $e) {
            error_log('ReliabilityController::verifyChecksum error: ' . $e->getMessage());

            return $this->response
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'error' => 'Internal server error during checksum verification',
                    'success' => false,
                ]));
        }
    }

    /**
     * GET /api/reliability/field-stats/{model}
     * Get corpus statistics for field scoring across all entities of a model
     */
    public function fieldStats(string $model = 'Products'): Response
    {
        $this->request->allowMethod(['get']);

        try {
            $fieldsTable = $this->fetchTable('ProductsReliabilityFields');
            $stats = $fieldsTable->getFieldStats($model);

            return $this->response
                ->withStatus(200)
                ->withStringBody(json_encode([
                    'success' => true,
                    'model' => $model,
                    'stats' => $stats,
                    'timestamp' => gmdate('c'),
                ]));
        } catch (Exception $e) {
            error_log('ReliabilityController::fieldStats error: ' . $e->getMessage());

            return $this->response
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'error' => 'Internal server error retrieving field statistics',
                    'success' => false,
                ]));
        }
    }

    /**
     * Generate unique request ID for tracking
     */
    private function generateRequestId(): string
    {
        return sprintf(
            'req_%s_%s',
            date('Ymd'),
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8),
        );
    }
}
