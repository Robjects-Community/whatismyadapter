<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\RateLimitService;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;
use Exception;
use Queue\Job\JobInterface;
use Queue\Job\JobTrait;

/**
 * Enhanced AI Operation Job
 *
 * Comprehensive background job system for AI operations with:
 * - Rate limiting enforcement
 * - Cost tracking and limits
 * - Automatic retry with exponential backoff
 * - Comprehensive error handling and logging
 * - Metrics recording
 * - Circuit breaker pattern for failing services
 */
class EnhancedAiOperationJob implements JobInterface
{
    use JobTrait;
    use LogTrait;

    /**
     * Maximum number of retry attempts
     */
    public const MAX_RETRIES = 3;

    /**
     * Base delay for exponential backoff (seconds)
     */
    public const BASE_DELAY = 60;

    /**
     * Circuit breaker failure threshold
     */
    public const CIRCUIT_BREAKER_THRESHOLD = 5;

    /**
     * Execute the AI operation job
     *
     * @param array $data Job data containing operation details
     * @return bool Success status
     */
    public function run(array $data): bool
    {
        $startTime = microtime(true);
        $operation = $data['operation'] ?? 'unknown';
        $service = $data['service'] ?? 'anthropic';
        $taskType = $data['task_type'] ?? 'general';
        $payload = $data['payload'] ?? [];
        $entityId = $data['entity_id'] ?? null;
        $entityType = $data['entity_type'] ?? null;

        $this->log("Starting AI operation: {$operation} for {$entityType} ID: {$entityId}", 'info');

        try {
            // Check circuit breaker status
            if ($this->isCircuitBreakerOpen($service)) {
                $this->log("Circuit breaker is open for service: {$service}", 'warning');
                $this->recordMetrics($taskType, $startTime, false, 'Circuit breaker open', $service);

                return false;
            }

            // Enforce rate limits
            $rateLimitService = new RateLimitService();
            $limitCheck = $rateLimitService->checkLimits($service);

            if (!$limitCheck['allowed']) {
                $this->log('Rate limit exceeded: ' . implode(', ', $limitCheck['reasons']), 'warning');
                $this->recordMetrics($taskType, $startTime, false, 'Rate limit exceeded', $service);

                // Reschedule job for later
                $this->rescheduleJob($data, 'Rate limit exceeded');

                return false;
            }

            // Execute the specific AI operation
            $result = $this->executeOperation($operation, $payload, $service);

            if ($result['success']) {
                // Record success metrics
                $this->recordMetrics(
                    $taskType,
                    $startTime,
                    true,
                    null,
                    $service,
                    $result['tokens_used'] ?? null,
                    $result['model_used'] ?? null,
                    $result['cost'] ?? null,
                );

                // Record cost for tracking
                if (isset($result['cost'])) {
                    $rateLimitService->recordCost($result['cost'], $service);
                }

                // Reset circuit breaker on success
                $this->resetCircuitBreaker($service);

                // Update entity if provided
                if ($entityId && $entityType && isset($result['data'])) {
                    $this->updateEntity($entityType, $entityId, $result['data']);
                }

                $this->log("AI operation completed successfully: {$operation}", 'info');

                return true;
            } else {
                // Handle failure
                $error = $result['error'] ?? 'Unknown error';
                $this->log("AI operation failed: {$operation} - {$error}", 'error');

                // Record failure metrics
                $this->recordMetrics($taskType, $startTime, false, $error, $service);

                // Update circuit breaker
                $this->recordCircuitBreakerFailure($service);

                // Determine if we should retry
                $currentAttempt = $data['attempt'] ?? 1;
                if ($currentAttempt < self::MAX_RETRIES && $this->shouldRetry($result)) {
                    $this->retryJob($data, $error, $currentAttempt);

                    return false;
                }

                return false;
            }
        } catch (Exception $e) {
            $this->log("Unexpected error in AI operation: {$e->getMessage()}", 'error');
            $this->recordMetrics($taskType, $startTime, false, $e->getMessage(), $service);
            $this->recordCircuitBreakerFailure($service);

            // Retry on unexpected errors
            $currentAttempt = $data['attempt'] ?? 1;
            if ($currentAttempt < self::MAX_RETRIES) {
                $this->retryJob($data, $e->getMessage(), $currentAttempt);
            }

            return false;
        }
    }

    /**
     * Execute the specific AI operation based on type
     *
     * @param string $operation Operation type
     * @param array $payload Operation payload
     * @param string $service AI service to use
     * @return array Result with success status and data
     */
    protected function executeOperation(string $operation, array $payload, string $service): array
    {
        switch ($operation) {
            case 'generate_seo':
                return $this->generateSeoContent($payload, $service);

            case 'analyze_image':
                return $this->analyzeImage($payload, $service);

            case 'generate_tags':
                return $this->generateTags($payload, $service);

            case 'analyze_sentiment':
                return $this->analyzeSentiment($payload, $service);

            case 'translate_content':
                return $this->translateContent($payload, $service);

            case 'generate_summary':
                return $this->generateSummary($payload, $service);

            default:
                return [
                    'success' => false,
                    'error' => "Unknown operation: {$operation}",
                ];
        }
    }

    /**
     * Generate SEO content using AI service
     */
    protected function generateSeoContent(array $payload, string $service): array
    {
        try {
            $seoService = $this->getServiceInstance($service, 'SeoContentGenerator');

            $result = $seoService->generateSeoContent(
                $payload['title'] ?? '',
                $payload['content'] ?? '',
                $payload['options'] ?? [],
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Analyze image using AI service
     */
    protected function analyzeImage(array $payload, string $service): array
    {
        try {
            $imageService = $this->getServiceInstance($service, 'ImageAnalyzer');

            $result = $imageService->analyzeImage(
                $payload['image_path'] ?? '',
                $payload['options'] ?? [],
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Generate tags using AI service
     */
    protected function generateTags(array $payload, string $service): array
    {
        try {
            $tagService = $this->getServiceInstance($service, 'TagGenerator');

            $result = $tagService->generateTags(
                $payload['content'] ?? '',
                $payload['options'] ?? [],
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Analyze sentiment using AI service
     */
    protected function analyzeSentiment(array $payload, string $service): array
    {
        try {
            $sentimentService = $this->getServiceInstance($service, 'CommentAnalyzer');

            $result = $sentimentService->analyzeSentiment(
                $payload['text'] ?? '',
                $payload['options'] ?? [],
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Translate content using AI service
     */
    protected function translateContent(array $payload, string $service): array
    {
        try {
            $translationService = $this->getServiceInstance($service, 'TranslationService');

            $result = $translationService->translate(
                $payload['text'] ?? '',
                $payload['target_language'] ?? 'en',
                $payload['source_language'] ?? 'auto',
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Generate summary using AI service
     */
    protected function generateSummary(array $payload, string $service): array
    {
        try {
            $summaryService = $this->getServiceInstance($service, 'ContentSummarizer');

            $result = $summaryService->summarize(
                $payload['content'] ?? '',
                $payload['options'] ?? [],
            );

            return [
                'success' => true,
                'data' => $result,
                'tokens_used' => $result['meta']['tokens_used'] ?? null,
                'model_used' => $result['meta']['model_used'] ?? null,
                'cost' => $result['meta']['cost'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'retry' => $this->isRetryableError($e),
            ];
        }
    }

    /**
     * Get service instance for the specified service and class
     *
     * @param string $service Service namespace (e.g. 'Anthropic', 'Google')
     * @param string $serviceClass Concrete service class name (e.g. 'SeoContentGenerator')
     * @return object Instance of the resolved service
     * @throws \Exception When class does not exist
     */
    protected function getServiceInstance(string $service, string $serviceClass): object
    {
        $className = '\\App\\Service\\Api\\' . ucfirst($service) . "\\{$serviceClass}";

        if (!class_exists($className)) {
            throw new Exception("Service class not found: {$className}");
        }

        return new $className();
    }

    /**
     * Record metrics for the operation
     */
    protected function recordMetrics(
        string $taskType,
        float $startTime,
        bool $success,
        ?string $errorMessage = null,
        string $service = 'anthropic',
        ?int $tokensUsed = null,
        ?string $modelUsed = null,
        ?float $cost = null,
    ): void {
        if (!SettingsManager::read('AI.enableMetrics', true)) {
            return;
        }

        try {
            $aiMetricsTable = TableRegistry::getTableLocator()->get('AiMetrics');
            $executionTime = (microtime(true) - $startTime) * 1000;

            $metric = $aiMetricsTable->newEntity([
                'task_type' => $taskType,
                'execution_time_ms' => (int)$executionTime,
                'tokens_used' => $tokensUsed,
                'model_used' => $modelUsed,
                'success' => $success,
                'error_message' => $errorMessage,
                'cost_usd' => $cost,
            ]);

            $aiMetricsTable->save($metric);
        } catch (Exception $e) {
            $this->log("Failed to record metrics: {$e->getMessage()}", 'error');
        }
    }

    /**
     * Update entity with AI operation results
     */
    protected function updateEntity(string $entityType, string $entityId, array $data): void
    {
        try {
            $table = TableRegistry::getTableLocator()->get($entityType);
            $entity = $table->get($entityId);

            // Apply the AI-generated data to the entity
            $entity = $table->patchEntity($entity, $data);

            if (!$table->save($entity)) {
                $this->log('Failed to update entity: ' . json_encode($entity->getErrors()), 'error');
            }
        } catch (Exception $e) {
            $this->log("Error updating entity: {$e->getMessage()}", 'error');
        }
    }

    /**
     * Retry the job with exponential backoff
     */
    protected function retryJob(array $data, string $reason, int $currentAttempt): void
    {
        $nextAttempt = $currentAttempt + 1;
        $delay = self::BASE_DELAY * pow(2, $currentAttempt - 1); // Exponential backoff

        $data['attempt'] = $nextAttempt;
        $data['retry_reason'] = $reason;
        $data['previous_attempts'] = ($data['previous_attempts'] ?? []);
        $data['previous_attempts'][] = [
            'attempt' => $currentAttempt,
            'reason' => $reason,
            'timestamp' => date('c'),
        ];

        // Queue the retry
        $queueJobsTable = TableRegistry::getTableLocator()->get('Queue.QueuedJobs');
        $queueJobsTable->createJob(self::class, $data, [
            'notBefore' => time() + $delay,
            'group' => 'ai_operations',
        ]);

        $message = sprintf(
            'Retrying AI operation (attempt %d/%d) in %d seconds: %s',
            $nextAttempt,
            self::MAX_RETRIES,
            $delay,
            (string)$reason,
        );
        $this->log($message, 'info');
    }

    /**
     * Reschedule job for later due to rate limiting
     */
    protected function rescheduleJob(array $data, string $reason): void
    {
        $delay = 3600; // Reschedule for next hour

        // Queue the rescheduled job
        $queueJobsTable = TableRegistry::getTableLocator()->get('Queue.QueuedJobs');
        $queueJobsTable->createJob(self::class, $data, [
            'notBefore' => time() + $delay,
            'group' => 'ai_operations',
        ]);

        $this->log("Rescheduling AI operation for next hour: {$reason}", 'info');
    }

    /**
     * Check if error should trigger a retry
     */
    protected function shouldRetry(array $result): bool
    {
        return $result['retry'] ?? true;
    }

    /**
     * Determine if an exception represents a retryable error
     */
    protected function isRetryableError(Exception $e): bool
    {
        $message = $e->getMessage();

        // Rate limiting, temporary network issues, etc.
        $retryablePatterns = [
            '/rate limit/i',
            '/timeout/i',
            '/connection/i',
            '/temporary/i',
            '/service unavailable/i',
            '/502/i',
            '/503/i',
            '/504/i',
        ];

        foreach ($retryablePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Circuit breaker implementation
     */
    protected function isCircuitBreakerOpen(string $service): bool
    {
        $key = "circuit_breaker_{$service}";
        $failures = Cache::read($key) ?? 0;

        return $failures >= self::CIRCUIT_BREAKER_THRESHOLD;
    }

    /**
     * Record a circuit breaker failure for a service.
     *
     * @param string $service Service key
     * @return void
     */
    protected function recordCircuitBreakerFailure(string $service): void
    {
        $key = "circuit_breaker_{$service}";
        $failures = Cache::read($key) ?? 0;

        Cache::write($key, $failures + 1, '+1 hour');
    }

    /**
     * Reset the circuit breaker for a service.
     *
     * @param string $service Service key
     * @return void
     */
    protected function resetCircuitBreaker(string $service): void
    {
        $key = "circuit_breaker_{$service}";
        Cache::delete($key);
    }
}
