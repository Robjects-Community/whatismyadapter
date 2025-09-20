<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Service\IpSecurityService;
use App\Utility\SettingsManager;
use Cake\Http\Response;
use Cake\Log\LogTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * IP Blocker Middleware
 *
 * Provides comprehensive IP-based security including:
 * - Proper IP detection with proxy support
 * - IP blocking based on database rules
 * - Suspicious request pattern detection
 * - Progressive blocking for repeat offenders
 */
class IpBlockerMiddleware implements MiddlewareInterface
{
    use LogTrait;

    private IpSecurityService $ipSecurity;

    /**
     * Constructor
     *
     * @param \App\Service\IpSecurityService|null $ipSecurity IP security service instance
     */
    public function __construct(?IpSecurityService $ipSecurity = null)
    {
        $this->ipSecurity = $ipSecurity ?? new IpSecurityService();
    }

    /**
     * Process the request through the middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get client IP using the service
        $clientIp = $this->ipSecurity->getClientIp($request);

        // Check if we should block when IP cannot be determined
        if (!$clientIp) {
            $blockOnNoIp = SettingsManager::read('Security.blockOnNoIp', true);

            if ($blockOnNoIp) {
                $this->log('Request blocked: Unable to determine client IP', 'warning', ['group_name' => 'security']);

                return $this->createBlockedResponse(
                    __('Access Denied: Unable to verify request origin.'),
                );
            }

            // If not blocking on no IP, allow the request but log it
            $this->log(
                'Request allowed despite missing IP - consider enabling blockOnNoIp',
                'info',
                ['group_name' => 'security'],
            );

            return $handler->handle($request);
        }

        // Store IP in request for use by other components (like RateLimitMiddleware)
        $request = $request->withAttribute('clientIp', $clientIp);

        // Check if IP is blocked
        if ($this->ipSecurity->isIpBlocked($clientIp)) {
            $this->log("Blocked request from banned IP: {$clientIp}", 'info', ['group_name' => 'security']);

            return $this->createBlockedResponse(
                __('Access Denied: Your IP address has been blocked due to suspicious activity.'),
            );
        }

        // Check for suspicious patterns using the full request object
        if ($this->ipSecurity->isSuspiciousRequest($request)) {
            $uri = $request->getUri();
            $route = $uri->getPath();
            $query = $uri->getQuery() ?? '';

            $this->ipSecurity->trackSuspiciousActivity($clientIp, $route, $query);

            $this->log(
                "Suspicious request detected from {$clientIp}: {$route}?{$query}",
                'warning',
                ['group_name' => 'security'],
            );

            return $this->createBlockedResponse(
                __('Access Denied: Suspicious request detected.'),
            );
        }

        // Note: Rate limiting is now handled by RateLimitMiddleware

        return $handler->handle($request);
    }

    /**
     * Create a blocked response with appropriate format
     *
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    private function createBlockedResponse(string $message, int $statusCode = 403): ResponseInterface
    {
        $response = new Response();

        // Set security headers
        $response = $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block');

        // Check if request expects JSON
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($acceptHeader, 'application/json') !== false) {
            return $response
                ->withStatus($statusCode)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'error' => $message,
                    'code' => $statusCode,
                ]));
        }

        // For HTML responses
        return $response
            ->withStatus($statusCode)
            ->withType('text/html')
            ->withStringBody($message);
    }
}
