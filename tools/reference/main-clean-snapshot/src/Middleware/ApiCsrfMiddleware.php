<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Custom CSRF middleware that exempts API routes
 */
class ApiCsrfMiddleware implements MiddlewareInterface
{
    private CsrfProtectionMiddleware $csrfMiddleware;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->csrfMiddleware = new CsrfProtectionMiddleware([
            'httponly' => true,
        ]);
    }

    /**
     * Process the request and handle CSRF protection
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if this is an API request
        $uri = $request->getUri()->getPath();
        $prefix = $request->getParam('prefix');

        // Skip CSRF for API routes
        if (strpos($uri, '/api/') === 0 || $prefix === 'Api') {
            return $handler->handle($request);
        }

        // Apply CSRF protection for non-API routes
        return $this->csrfMiddleware->process($request, $handler);
    }
}
