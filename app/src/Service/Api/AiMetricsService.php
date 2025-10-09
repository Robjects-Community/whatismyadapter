<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Model\Table\AiMetricsTable;
use App\Utility\SettingsManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Exception;

/**
 * Centralized service for recording AI API metrics.
 *
 * This service provides a consistent interface for recording metrics across
 * different AI API services (Anthropic, Google Translate, etc.).
 */
class AiMetricsService
{
    /**
     * @var \App\Model\Table\AiMetricsTable
     */
    private AiMetricsTable $aiMetricsTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \App\Model\Table\AiMetricsTable $table */
        $table = TableRegistry::getTableLocator()->get('AiMetrics');
        $this->aiMetricsTable = $table;
    }

    /**
     * Records metrics for an AI API operation.
     *
     * @param string $taskType The type of task (e.g., 'google_translate', 'anthropic_seo')
     * @param int $executionTimeMs The execution time in milliseconds
     * @param bool $success Whether the operation was successful
     * @param string|null $errorMessage Error message if the operation failed
     * @param int|null $tokensUsed Number of tokens used (if applicable)
     * @param float|null $costUsd Cost in USD (if calculable)
     * @param string|null $modelUsed The model used (if applicable)
     * @return bool Whether the metrics were successfully recorded
     */
    public function recordMetrics(
        string $taskType,
        int $executionTimeMs,
        bool $success,
        ?string $errorMessage = null,
        ?int $tokensUsed = null,
        ?float $costUsd = null,
        ?string $modelUsed = null,
    ): bool {
        // Check if metrics tracking is enabled
        if (!SettingsManager::read('AI.enableMetrics', true)) {
            return true; // Silently skip if disabled
        }

        try {
            $metrics = $this->aiMetricsTable->newEntity([
                'id' => Text::uuid(),
                'task_type' => $taskType,
                'execution_time_ms' => $executionTimeMs,
                'tokens_used' => $tokensUsed,
                'cost_usd' => $costUsd,
                'success' => $success,
                'error_message' => $errorMessage,
                'model_used' => $modelUsed,
            ]);

            return (bool)$this->aiMetricsTable->save($metrics);
        } catch (Exception $e) {
            // Log the error but don't throw to avoid breaking the main operation
            error_log('Failed to record AI metrics: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Calculate estimated cost for Google Translate API operations.
     *
     * Based on Google Cloud Translation pricing:
     * - Basic edition: $20 per 1 million characters
     * - Advanced edition: $20 per 1 million characters (same base rate)
     *
     * @param int $characterCount Number of characters translated
     * @return float Estimated cost in USD
     */
    public function calculateGoogleTranslateCost(int $characterCount): float
    {
        // Google Translate pricing: $20 per 1,000,000 characters
        return $characterCount / 1000000 * 20.0;
    }

    /**
     * Count characters in an array of strings for cost calculation.
     *
     * @param array $strings Array of strings to count
     * @return int Total character count
     */
    public function countCharacters(array $strings): int
    {
        $totalChars = 0;
        foreach ($strings as $string) {
            $totalChars += mb_strlen($string ?? '', 'UTF-8');
        }

        return $totalChars;
    }

    /**
     * Get the current daily cost total from metrics.
     *
     * @return float Total cost for today in USD
     */
    public function getDailyCost(): float
    {
        $today = FrozenTime::now()->format('Y-m-d');
        $tomorrow = FrozenTime::now()->addDays(1)->format('Y-m-d');

        return $this->aiMetricsTable->getCostsByDateRange($today . ' 00:00:00', $tomorrow . ' 00:00:00');
    }

    /**
     * Check if daily cost limit has been reached.
     *
     * @return bool True if limit has been reached
     */
    public function isDailyCostLimitReached(): bool
    {
        $dailyLimit = (float)SettingsManager::read('AI.dailyCostLimit', 2.50);

        // If limit is 0, consider it unlimited
        if ($dailyLimit <= 0) {
            return false;
        }

        return $this->getDailyCost() >= $dailyLimit;
    }

    /**
     * Send cost alert if enabled and threshold is reached.
     *
     * @param float $currentCost Current daily cost
     * @param float $newCost Cost of new operation
     * @return void
     */
    public function checkCostAlert(float $currentCost, float $newCost): void
    {
        if (!SettingsManager::read('AI.enableCostAlerts', true)) {
            return;
        }

        $dailyLimit = (float)SettingsManager::read('AI.dailyCostLimit', 2.50);
        $alertThreshold = $dailyLimit * 0.8; // Alert at 80% of limit

        if ($currentCost < $alertThreshold && ($currentCost + $newCost) >= $alertThreshold) {
            // TODO: Implement email alert functionality
            error_log(
                'AI Cost Alert: Daily spending has reached $' . ($currentCost + $newCost) . ' (80% of daily limit)',
            );
        }
    }

    /**
     * Get metrics summary for dashboard.
     *
     * @return array Summary metrics
     */
    public function getMetricsSummary(): array
    {
        // Get all-time summary (from beginning of time to now)
        $startDate = '1970-01-01 00:00:00';
        $endDate = date('Y-m-d H:i:s');
        $taskSummary = $this->aiMetricsTable->getTaskTypeSummary($startDate, $endDate);

        // Calculate totals
        $totalCalls = 0;
        $totalCost = 0.0;

        foreach ($taskSummary as $task) {
            $totalCalls += (int)$task['count'];
            $totalCost += (float)$task['total_cost'];
        }

        // Calculate success rate from all metrics
        $successfulCalls = $this->aiMetricsTable->find()
            ->where(['success' => true])
            ->count();

        $successRate = $totalCalls > 0 ? $successfulCalls / $totalCalls * 100 : 0;

        return [
            'totalCalls' => $totalCalls,
            'successRate' => $successRate,
            'totalCost' => $totalCost,
        ];
    }

    /**
     * Get real-time metrics data for dashboard.
     *
     * @param string $timeframe Timeframe ('1h', '24h', '7d', '30d')
     * @return array Real-time data
     */
    public function getRealtimeData(string $timeframe): array
    {
        // Get summary for the specified timeframe
        $summary = $this->getMetricsSummary();

        return [
            'metrics' => $summary,
            'rateLimit' => [
                'current' => $this->getDailyCost(),
                'limit' => (float)SettingsManager::read('AI.dailyCostLimit', 2.50),
                'isLimited' => $this->isDailyCostLimitReached(),
            ],
            'queueStatus' => [
                'active' => 0, // Would integrate with queue system
                'pending' => 0,
                'failed' => 0,
            ],
            'recentActivity' => $this->getRecentErrors(5),
        ];
    }

    /**
     * Get task type statistics.
     *
     * @return array Task type statistics
     */
    public function getTaskTypeStatistics(): array
    {
        // Get all-time summary (from beginning of time to now)
        $startDate = '1970-01-01 00:00:00';
        $endDate = date('Y-m-d H:i:s');
        $taskSummary = $this->aiMetricsTable->getTaskTypeSummary($startDate, $endDate);

        // Transform to expected format for tests
        $result = [];
        foreach ($taskSummary as $task) {
            $result[$task['task_type']] = [
                'count' => (int)$task['count'],
                'avg_time' => (float)$task['avg_time'],
                'success_rate' => (float)$task['success_rate'],
                'total_cost' => (float)$task['total_cost'],
                'total_tokens' => (int)$task['total_tokens'],
            ];
        }

        return $result;
    }

    /**
     * Get recent error metrics.
     *
     * @param int $limit Number of recent errors to get
     * @return array Recent error metrics
     */
    private function getRecentErrors(int $limit = 10): array
    {
        try {
            $errors = $this->aiMetricsTable->find()
                ->where(['success' => false])
                ->where(['error_message IS NOT' => null])
                ->orderByDesc('created')
                ->limit($limit)
                ->select(['task_type', 'error_message', 'created'])
                ->toArray();

            return array_map(function ($error) {
                return [
                    'task_type' => $error->task_type,
                    'error_message' => $error->error_message,
                    'created' => $error->created,
                ];
            }, $errors);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Calculate Anthropic API costs based on token usage.
     *
     * @param int $inputTokens Number of input tokens
     * @param int $outputTokens Number of output tokens
     * @return float Estimated cost in USD
     */
    private function calculateAnthropicCost(int $inputTokens, int $outputTokens): float
    {
        // Anthropic pricing (as of 2024):
        // Input: $3 per million tokens
        // Output: $15 per million tokens
        $inputCost = $inputTokens / 1000000 * 3.0;
        $outputCost = $outputTokens / 1000000 * 15.0;

        return $inputCost + $outputCost;
    }
}
