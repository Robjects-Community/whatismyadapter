<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Health Controller
 *
 * Provides health check endpoints for DigitalOcean App Platform monitoring
 */
class HealthController extends AppController
{
    /**
     * Initialize method
     * 
     * Skip authentication for health checks
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Skip authentication for health checks
        $this->Authentication->allowUnauthenticated(['healthz', 'readyz']);
    }

    /**
     * Health check endpoint (liveness probe)
     * 
     * Simple health check that returns 200 OK without dependencies
     * Used by App Platform to determine if the application is alive
     * 
     * @return \Cake\Http\Response
     */
    public function healthz(): Response
    {
        $response = [
            'status' => 'healthy',
            'service' => 'WillowCMS',
            'timestamp' => date('c'),
            'environment' => env('APP_ENV', 'unknown')
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response, JSON_PRETTY_PRINT));
    }

    /**
     * Readiness check endpoint (readiness probe) 
     * 
     * More comprehensive health check that verifies dependencies
     * Used to determine if the application is ready to receive traffic
     * 
     * @return \Cake\Http\Response
     */
    public function readyz(): Response
    {
        $checks = [];
        $overall_status = 'healthy';
        $status_code = 200;

        // Check database connectivity
        try {
            $connection = $this->getTableLocator()->get('Users')->getConnection();
            $connection->execute('SELECT 1');
            $checks['database'] = 'healthy';
        } catch (\Exception $e) {
            $checks['database'] = 'unhealthy: ' . $e->getMessage();
            $overall_status = 'unhealthy';
            $status_code = 503;
        }

        // Check Redis connectivity (if configured)
        try {
            if (class_exists('\Redis') && env('REDIS_URL')) {
                $redis = new \Redis();
                $redis->connect(env('REDIS_HOST', 'redis'), (int)env('REDIS_PORT', 6379));
                if (env('REDIS_PASSWORD')) {
                    $redis->auth(env('REDIS_PASSWORD'));
                }
                $redis->ping();
                $redis->close();
                $checks['redis'] = 'healthy';
            } else {
                $checks['redis'] = 'not_configured';
            }
        } catch (\Exception $e) {
            $checks['redis'] = 'unhealthy: ' . $e->getMessage();
            $overall_status = 'unhealthy';
            $status_code = 503;
        }

        // Check file system write permissions
        try {
            $temp_file = TMP . 'health_check_' . time() . '.tmp';
            if (file_put_contents($temp_file, 'health check') !== false) {
                unlink($temp_file);
                $checks['filesystem'] = 'healthy';
            } else {
                $checks['filesystem'] = 'unhealthy: cannot write to temp directory';
                $overall_status = 'unhealthy';
                $status_code = 503;
            }
        } catch (\Exception $e) {
            $checks['filesystem'] = 'unhealthy: ' . $e->getMessage();
            $overall_status = 'unhealthy';
            $status_code = 503;
        }

        $response = [
            'status' => $overall_status,
            'service' => 'WillowCMS',
            'timestamp' => date('c'),
            'environment' => env('APP_ENV', 'unknown'),
            'checks' => $checks
        ];

        return $this->response
            ->withStatus($status_code)
            ->withType('application/json')
            ->withStringBody(json_encode($response, JSON_PRETTY_PRINT));
    }
}