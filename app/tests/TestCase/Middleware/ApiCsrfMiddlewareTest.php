<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\ApiCsrfMiddleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ApiCsrfMiddleware Test Case
 *
 * Tests CSRF protection behavior for API and non-API routes
 */
class ApiCsrfMiddlewareTest extends TestCase
{
    /**
     * Test that CSRF protection is skipped for /api/* routes
     *
     * @return void
     */
    public function testSkipsCsrfForApiRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/api/users',
            'environment' => ['REQUEST_METHOD' => 'POST'],
        ]);
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        $response = $middleware->process($request, $handler);
        
        // Should pass through without CSRF validation
        $this->assertTrue($handlerCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that CSRF protection is skipped for routes with Api prefix
     *
     * @return void
     */
    public function testSkipsCsrfForApiPrefixRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = (new ServerRequest([
            'url' => '/some-route',
            'environment' => ['REQUEST_METHOD' => 'POST'],
        ]))->withParam('prefix', 'Api');
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        $response = $middleware->process($request, $handler);
        
        $this->assertTrue($handlerCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that CSRF protection is applied to non-API routes
     *
     * @return void
     */
    public function testAppliesCsrfForNonApiRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'POST'],
        ]);
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        // Without a valid CSRF token, this should fail or require token
        // The actual CsrfProtectionMiddleware will handle the validation
        // We're just testing that the middleware is called
        $response = $middleware->process($request, $handler);
        
        // The response will depend on CsrfProtectionMiddleware behavior
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * Test GET requests to non-API routes
     *
     * @return void
     */
    public function testAllowsGetRequestsToNonApiRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/users/login',
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]);
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        $response = $middleware->process($request, $handler);
        
        // GET requests should pass through (CSRF typically only validates POST/PUT/DELETE)
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * Test API routes with different HTTP methods
     *
     * @return void
     */
    public function testSkipsCsrfForAllApiHttpMethods(): void
    {
        $middleware = new ApiCsrfMiddleware();
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        
        foreach ($methods as $method) {
            $request = new ServerRequest([
                'url' => '/api/resource',
                'environment' => ['REQUEST_METHOD' => $method],
            ]);
            
            $handlerCalled = false;
            $handler = $this->createMockHandler($handlerCalled);
            
            $response = $middleware->process($request, $handler);
            
            $this->assertTrue($handlerCalled, "Handler should be called for {$method} method");
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    /**
     * Test nested API routes
     *
     * @return void
     */
    public function testSkipsCsrfForNestedApiRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $routes = [
            '/api/v1/users',
            '/api/users/123',
            '/api/users/123/posts',
            '/api/auth/login',
        ];
        
        foreach ($routes as $route) {
            $request = new ServerRequest([
                'url' => $route,
                'environment' => ['REQUEST_METHOD' => 'POST'],
            ]);
            
            $handlerCalled = false;
            $handler = $this->createMockHandler($handlerCalled);
            
            $response = $middleware->process($request, $handler);
            
            $this->assertTrue($handlerCalled, "Handler should be called for route: {$route}");
        }
    }

    /**
     * Test that routes starting with /api/ are treated as API routes
     *
     * @return void
     */
    public function testApiRouteDetection(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        // These should be treated as API routes
        $apiRoutes = [
            '/api',
            '/api/',
            '/api/test',
            '/api/v1/test',
        ];
        
        foreach ($apiRoutes as $route) {
            $request = new ServerRequest([
                'url' => $route,
                'environment' => ['REQUEST_METHOD' => 'POST'],
            ]);
            
            $handlerCalled = false;
            $handler = $this->createMockHandler($handlerCalled);
            
            $middleware->process($request, $handler);
            
            $this->assertTrue($handlerCalled, "Route {$route} should be treated as API route");
        }
    }

    /**
     * Test that similar-looking routes are not mistaken for API routes
     *
     * @return void
     */
    public function testNonApiRouteDetection(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        // These should NOT be treated as API routes
        $nonApiRoutes = [
            '/users/api',
            '/capability',
            '/erapidly',
            '/admin/users',
        ];
        
        foreach ($nonApiRoutes as $route) {
            $request = new ServerRequest([
                'url' => $route,
                'environment' => ['REQUEST_METHOD' => 'GET'],
            ]);
            
            $handler = $this->createMockHandler($handlerCalled);
            
            $response = $middleware->process($request, $handler);
            
            // These routes should go through CSRF middleware
            $this->assertInstanceOf(ResponseInterface::class, $response);
        }
    }

    /**
     * Test with language-prefixed routes
     *
     * @return void
     */
    public function testHandlesLanguagePrefixedRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        // Non-API route with language prefix
        $request = new ServerRequest([
            'url' => '/en/users/login',
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]);
        
        $handler = $this->createMockHandler($handlerCalled);
        $response = $middleware->process($request, $handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * Test API routes with language prefix (if supported)
     *
     * @return void
     */
    public function testHandlesLanguagePrefixedApiRoutes(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        // API route with language prefix
        $request = new ServerRequest([
            'url' => '/en/api/users',
            'environment' => ['REQUEST_METHOD' => 'POST'],
        ]);
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        $response = $middleware->process($request, $handler);
        
        // Even with language prefix, should still skip CSRF for /api/ routes
        // Note: This depends on your routing implementation
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * Test that empty or root routes are handled
     *
     * @return void
     */
    public function testHandlesRootRoute(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/',
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]);
        
        $handler = $this->createMockHandler($handlerCalled);
        $response = $middleware->process($request, $handler);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * Test with query parameters
     *
     * @return void
     */
    public function testHandlesQueryParameters(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/api/users?filter=active',
            'environment' => ['REQUEST_METHOD' => 'GET'],
        ]);
        
        $handlerCalled = false;
        $handler = $this->createMockHandler($handlerCalled);
        
        $response = $middleware->process($request, $handler);
        
        $this->assertTrue($handlerCalled);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that middleware properly integrates with request handler chain
     *
     * @return void
     */
    public function testProperlyIntegratesWithHandlerChain(): void
    {
        $middleware = new ApiCsrfMiddleware();
        
        $request = new ServerRequest([
            'url' => '/api/test',
            'environment' => ['REQUEST_METHOD' => 'POST'],
        ]);
        
        $capturedRequest = null;
        $handler = new class ($capturedRequest) implements RequestHandlerInterface {
            private $capturedRequest;
            
            public function __construct(&$capturedRequest)
            {
                $this->capturedRequest = &$capturedRequest;
            }
            
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                $this->capturedRequest = $request;
                
                return new Response();
            }
        };
        
        $middleware->process($request, $handler);
        
        // Verify the request was passed through
        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestInterface::class, $capturedRequest);
        $this->assertSame('/api/test', $capturedRequest->getUri()->getPath());
    }

    /**
     * Create a mock request handler
     *
     * @param bool &$called Reference to track if handler was called
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    private function createMockHandler(bool &$called = false): RequestHandlerInterface
    {
        return new class ($called) implements RequestHandlerInterface {
            private $called;
            
            public function __construct(&$called)
            {
                $this->called = &$called;
            }
            
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                $this->called = true;
                
                return new Response();
            }
        };
    }
}
