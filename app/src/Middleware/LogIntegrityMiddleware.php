<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\LogChecksumService;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Queue\QueueManager;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * LogIntegrityMiddleware
 *
 * Middleware that automatically verifies log file integrity at regular intervals
 * and logs any detected anomalies or tampering attempts.
 */
class LogIntegrityMiddleware implements MiddlewareInterface
{
    private LogChecksumService $checksumService;
    private const VERIFICATION_INTERVAL = 3600; // 1 hour in seconds
    private const CACHE_KEY_PREFIX = 'log_integrity_';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->checksumService = new LogChecksumService();
    }

    /**
     * Process the request and verify log integrity if needed
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler Request handler
     * @return \Psr\Http\Message\ResponseInterface Response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if log integrity verification is enabled
        if (Configure::read('LogIntegrity.enabled', true)) {
            $this->verifyLogIntegrityIfNeeded();
        }

        return $handler->handle($request);
    }

    /**
     * Verify log integrity if enough time has passed since last verification
     *
     * @return void
     */
    private function verifyLogIntegrityIfNeeded(): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX . 'last_verification';
        $cache = cache('default');

        $lastVerification = $cache->get($cacheKey, 0);
        $currentTime = time();

        // Check if we need to verify (based on interval)
        if ($currentTime - $lastVerification >= self::VERIFICATION_INTERVAL) {
            $this->performLogIntegrityCheck();
            $cache->set($cacheKey, $currentTime);
        }
    }

    /**
     * Perform actual log integrity check
     *
     * @return void
     */
    private function performLogIntegrityCheck(): void
    {
        try {
            $report = $this->checksumService->getIntegrityReport();

            // Log the verification attempt
            Log::info('Automatic log integrity verification completed', [
                'overall_status' => $report['overall_status'],
                'summary' => $report['summary'],
                'timestamp' => $report['timestamp'],
            ]);

            // Handle different integrity statuses
            switch ($report['overall_status']) {
                case 'CRITICAL':
                    $this->handleCriticalIntegrityIssue($report);
                    break;

                case 'WARNING':
                    $this->handleWarningIntegrityIssue($report);
                    break;

                case 'INFO':
                    $this->handleInfoIntegrityIssue($report);
                    break;

                case 'OK':
                    // All good, just log success
                    Log::debug('Log integrity verification: All log files verified successfully');
                    break;
            }

            // Store last verification results for monitoring
            $this->storeVerificationResults($report);
        } catch (Exception $e) {
            Log::error('Log integrity verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle critical integrity issues
     *
     * @param array $report Integrity report
     * @return void
     */
    private function handleCriticalIntegrityIssue(array $report): void
    {
        $message = 'CRITICAL: Log file integrity compromised!';

        // Log critical alert
        Log::critical($message, [
            'corrupted_files' => array_keys($report['details']['corrupted']),
            'failed_files' => array_keys($report['details']['failed']),
            'summary' => $report['summary'],
            'recommendations' => [
                'Investigate corrupted files immediately',
                'Check for unauthorized access or system compromise',
                'Consider restoring from verified backup',
                'Review system security logs',
            ],
        ]);

        // Send notification if configured
        $this->sendIntegrityAlert('critical', $report);

        // Set cache flag for admin dashboard alerts
        $this->setCriticalAlertFlag($report);
    }

    /**
     * Handle warning-level integrity issues
     *
     * @param array $report Integrity report
     * @return void
     */
    private function handleWarningIntegrityIssue(array $report): void
    {
        $message = 'WARNING: Log file integrity issues detected';

        Log::warning($message, [
            'failed_files' => array_keys($report['details']['failed']),
            'summary' => $report['summary'],
            'recommendations' => [
                'Review failed files for unexpected modifications',
                'Regenerate checksums if modifications are legitimate',
                'Monitor for recurring integrity failures',
            ],
        ]);

        $this->sendIntegrityAlert('warning', $report);
    }

    /**
     * Handle info-level integrity issues
     *
     * @param array $report Integrity report
     * @return void
     */
    private function handleInfoIntegrityIssue(array $report): void
    {
        Log::info('Log integrity check: Missing checksums detected', [
            'missing_files' => array_keys($report['details']['missing']),
            'summary' => $report['summary'],
            'recommendation' => 'Run checksum generation to create missing checksums',
        ]);
    }

    /**
     * Send integrity alert notification
     *
     * @param string $level Alert level
     * @param array $report Integrity report
     * @return void
     */
    private function sendIntegrityAlert(string $level, array $report): void
    {
        // Check if notifications are enabled
        if (!Configure::read('LogIntegrity.notifications.enabled', false)) {
            return;
        }

        $recipients = Configure::read('LogIntegrity.notifications.recipients', []);
        if (empty($recipients)) {
            return;
        }

        // Queue notification email job
        $this->queueIntegrityNotification($level, $report, $recipients);
    }

    /**
     * Queue integrity notification email
     *
     * @param string $level Alert level
     * @param array $report Integrity report
     * @param array $recipients Email recipients
     * @return void
     */
    private function queueIntegrityNotification(string $level, array $report, array $recipients): void
    {
        try {
            // Import the job class locally to avoid autoloading issues
            if (class_exists('Queue\Job\Job')) {
                $jobData = [
                    'level' => $level,
                    'report' => $report,
                    'recipients' => $recipients,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];

                // Queue the notification job
                QueueManager::push('LogIntegrityNotificationJob', $jobData);

                Log::debug('Log integrity notification queued', ['level' => $level]);
            }
        } catch (Exception $e) {
            Log::error('Failed to queue log integrity notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set critical alert flag in cache for admin dashboard
     *
     * @param array $report Integrity report
     * @return void
     */
    private function setCriticalAlertFlag(array $report): void
    {
        $cache = cache('default');
        $alertData = [
            'level' => 'critical',
            'message' => 'Log file integrity compromised',
            'timestamp' => time(),
            'details' => $report['summary'],
            'expires' => time() + (24 * 3600), // 24 hours
        ];

        $cache->set('log_integrity_critical_alert', $alertData, '+24 hours');
    }

    /**
     * Store verification results for monitoring and trending
     *
     * @param array $report Integrity report
     * @return void
     */
    private function storeVerificationResults(array $report): void
    {
        $cache = cache('default');

        // Store last 10 verification results
        $historyKey = self::CACHE_KEY_PREFIX . 'history';
        $history = $cache->get($historyKey, []);

        // Add current result
        $history[] = [
            'timestamp' => time(),
            'status' => $report['overall_status'],
            'summary' => $report['summary'],
        ];

        // Keep only last 10 results
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        $cache->set($historyKey, $history, '+7 days');
    }

    /**
     * Get integrity verification history
     *
     * @return array Verification history
     */
    public static function getVerificationHistory(): array
    {
        $cache = cache('default');

        return $cache->get(self::CACHE_KEY_PREFIX . 'history', []);
    }

    /**
     * Get current critical alert if any
     *
     * @return array|null Critical alert data or null
     */
    public static function getCriticalAlert(): ?array
    {
        $cache = cache('default');
        $alert = $cache->get('log_integrity_critical_alert');

        if ($alert && $alert['expires'] > time()) {
            return $alert;
        }

        return null;
    }

    /**
     * Clear critical alert flag
     *
     * @return void
     */
    public static function clearCriticalAlert(): void
    {
        $cache = cache('default');
        $cache->delete('log_integrity_critical_alert');
    }
}
