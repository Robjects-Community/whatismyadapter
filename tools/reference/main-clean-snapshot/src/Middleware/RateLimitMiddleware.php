<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var array<string, array{limit: int, period: int}> Route-specific configurations
     */
    private array $routeConfigs = [];

    /**
     * @var int Default limit for routes not specifically configured
     */
    private int $defaultLimit;

    /**
     * @var int Default period for routes not specifically configured
     */
    private int $defaultPeriod;

    /**
     * @var bool Whether rate limiting is enabled
     */
    private bool $enabled;

    /**
     * Constructor
     *
     * @param array $config Configuration options (can be overridden for testing)
     */
    public function __construct(array $config = [])
    {
        // Check if rate limiting is enabled
        $this->enabled = (bool)SettingsManager::read('Security.enableRateLimiting', true);

        // Load global defaults from settings
        $this->defaultLimit = (int)SettingsManager::read('RateLimit.numberOfRequests', 100);
        $this->defaultPeriod = (int)SettingsManager::read('RateLimit.numberOfSeconds', 60);

        // Load route-specific configurations
        $this->loadRouteConfigs();

        // Allow config overrides (useful for testing)
        if (!empty($config)) {
            if (isset($config['enabled'])) {
                $this->enabled = $config['enabled'];
            }
            if (isset($config['defaultLimit'])) {
                $this->defaultLimit = $config['defaultLimit'];
            }
            if (isset($config['defaultPeriod'])) {
                $this->defaultPeriod = $config['defaultPeriod'];
            }
            if (isset($config['routes'])) {
                $this->routeConfigs = array_merge($this->routeConfigs, $config['routes']);
            }
        }
    }

    /**
     * Load route-specific configurations from settings
     *
     * @return void
     */
    private function loadRouteConfigs(): void
    {
        // Define the route patterns and their corresponding setting keys
        $routeSettings = [
            '/admin/*' => 'admin',
            '/users/login' => 'login',
            '/users/reset-password/*' => 'passwordReset',
            '/users/forgot-password' => 'passwordReset',
            '/users/confirm-email' => 'passwordReset',
            '/users/register' => 'register',
        ];

        foreach ($routeSettings as $route => $settingKey) {
            $limit = SettingsManager::read("RateLimit.{$settingKey}NumberOfRequests", null);
            $period = SettingsManager::read("RateLimit.{$settingKey}NumberOfSeconds", null);

            // Only add to configs if both settings exist
            if ($limit !== null && $period !== null) {
                $this->routeConfigs[$route] = [
                    'limit' => (int)$limit,
                    'period' => (int)$period,
                ];
            }
        }
    }

    /**
     * Process a server request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface The response.
     * @throws \App\Http\Exception\TooManyRequestsException If the rate limit is exceeded.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip if rate limiting is disabled
        if (!$this->enabled) {
            return $handler->handle($request);
        }

        // Set trust proxy attribute if configured
        if (SettingsManager::read('Security.trustProxy', false)) {
            $request = $request->withAttribute('trustProxy', true);
        }

        // Get client IP (will be set by IpBlockerMiddleware if it runs first)
        $ip = $request->getAttribute('clientIp') ?? $request->clientIp();

        if (!$ip) {
            // If we can't determine IP, let it through (IpBlockerMiddleware will handle this)
            return $handler->handle($request);
        }

        $route = $request->getUri()->getPath();
        $config = $this->getRouteConfig($route);

        if ($config !== null) {
            $key = 'rate_limit_' . md5($ip . '_' . $route);
            $rateData = $this->updateRateLimit($key, $config['period']);

            if ($rateData['count'] > $config['limit']) {
                $this->logViolation($ip, $route, $request->getUri()->getQuery(), $rateData, $config);

                throw new TooManyRequestsException(
                    __('Too many requests. Please try again later.'),
                    null,
                    $config['period'],
                );
            }
        }

        return $handler->handle($request);
    }

    /**
     * Get configuration for a specific route
     *
     * @param string $route The route to check
     * @return array{limit: int, period: int}|null Configuration or null if using defaults
     */
    private function getRouteConfig(string $route): ?array
    {
        // First check for exact match
        if (isset($this->routeConfigs[$route])) {
            return $this->routeConfigs[$route];
        }

        // Then check for wildcard matches
        foreach ($this->routeConfigs as $pattern => $config) {
            if (str_contains($pattern, '*')) {
                // Handle wildcard routes
                $escapedPattern = preg_quote($pattern, '#');
                $escapedPattern = str_replace('\*', '.*', $escapedPattern);

                // Allow optional language prefix
                $fullPattern = '#^(/[a-z]{2})?' . $escapedPattern . '$#';

                if (preg_match($fullPattern, $route)) {
                    return $config;
                }
            } else {
                // Allow optional language prefix for exact matches
                $fullPattern = '#^(/[a-z]{2})?' . preg_quote($pattern, '#') . '$#';
                if (preg_match($fullPattern, $route)) {
                    return $config;
                }
            }
        }

        // Return default configuration for all other routes
        return [
            'limit' => $this->defaultLimit,
            'period' => $this->defaultPeriod,
        ];
    }

    /**
     * Update rate limit data for a given key
     *
     * @param string $key Cache key
     * @param int $period Time period
     * @return array{count: int, start_time: int} Rate limit data
     */
    private function updateRateLimit(string $key, int $period): array
    {
        $rateData = Cache::read($key, 'rate_limit') ?: ['count' => 0, 'start_time' => time()];

        $currentTime = time();
        if ($currentTime - $rateData['start_time'] > $period) {
            $rateData = ['count' => 1, 'start_time' => $currentTime];
        } else {
            $rateData['count']++;
        }

        Cache::write($key, $rateData, 'rate_limit');

        return $rateData;
    }

    /**
     * Log rate limit violations
     *
     * @param string $ip IP address
     * @param string $route Request route
     * @param string $query Query string
     * @param array{count: int, start_time: int} $rateData Rate limit data
     * @param array{limit: int, period: int} $config Route configuration
     * @return void
     */
    private function logViolation(string $ip, string $route, string $query, array $rateData, array $config): void
    {
        Log::warning(__('Rate limit exceeded for IP: {0}', [$ip]), [
            'ip' => $ip,
            'route' => $route,
            'query' => $query,
            'count' => $rateData['count'],
            'limit' => $config['limit'],
            'period' => $config['period'],
            'group_name' => 'rate_limiting',
        ]);
    }
}
