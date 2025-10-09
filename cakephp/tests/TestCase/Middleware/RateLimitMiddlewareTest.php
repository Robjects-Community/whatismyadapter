<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Middleware\RateLimitMiddleware;
use App\Test\Traits\SecurityMiddlewareTestTrait;
use Cake\Cache\Cache;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddlewareTest extends TestCase
{
    use SecurityMiddlewareTestTrait;

    /**
     * @var \App\Middleware\RateLimitMiddleware
     */
    private RateLimitMiddleware $middleware;

    /**
     * Set up the test case.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Enable security middleware for these tests
        $this->enableSecurityMiddleware();

        // Clear the rate limit cache completely
        Cache::clear('rate_limit');

        // Configure the middleware with test-specific settings
        $this->middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 100,
            'defaultPeriod' => 60,
            'routes' => [
                '/users/login' => ['limit' => 2, 'period' => 60],
                '/users/register' => ['limit' => 2, 'period' => 60],
                '/pages/*' => ['limit' => 2, 'period' => 60],
            ],
        ]);
    }

    /**
     * Tear down
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Disable security middleware after tests
        $this->disableSecurityMiddleware();

        Cache::clear('rate_limit');
    }

    /**
     * Test rate limiting for general routes.
     */
    public function testRateLimitGeneralRoute()
    {
        $request = new ServerRequest(['url' => '/articles/view']);
        $request = $request->withEnv('REMOTE_ADDR', '127.0.0.1');
        $handler = $this->createMock(RequestHandlerInterface::class);

        // Since general routes have a high limit (100), all should pass
        $handler->expects($this->exactly(3))->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        // First request should pass
        $this->middleware->process($request, $handler);

        // Second request should pass
        $this->middleware->process($request, $handler);

        // Third request should pass since '/articles/view' has high rate limit
        $this->middleware->process($request, $handler);
    }

    /**
     * Test rate limiting for sensitive routes.
     */
    public function testRateLimitSensitiveRoute()
    {
        // Clear any existing rate limit data for this test
        //Cache::clear('rate_limit');

        $request = new ServerRequest(['url' => '/users/login']);
        $request = $request->withEnv('REMOTE_ADDR', '127.0.0.2'); // Different IP from other tests
        $handler = $this->createMock(RequestHandlerInterface::class);

        // With limit of 2, only 2 requests should pass
        $handler->expects($this->exactly(2))->method('handle')->willReturn($this->createMock(ResponseInterface::class));

        // First request should pass
        $this->middleware->process($request, $handler);

        // Second request should pass
        $this->middleware->process($request, $handler);

        // Third request should throw TooManyRequestsException
        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessage('Too many requests. Please try again later.');
        $this->middleware->process($request, $handler);
    }

    /**
     * Test that the rate limit is not exceeded for multiple different routes.
     */
    public function testDifferentRoutesDoNotAffectRateLimit()
    {
        $request1 = new ServerRequest(['url' => '/articles/view']);
        $request1 = $request1->withEnv('REMOTE_ADDR', '127.0.0.3');
        $request2 = new ServerRequest(['url' => '/articles/edit']);
        $request2 = $request2->withEnv('REMOTE_ADDR', '127.0.0.3');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $handler->method('handle')->willReturn($responseMock);

        // First request should pass
        $response1 = $this->middleware->process($request1, $handler);
        $this->assertSame($responseMock, $response1, 'First request did not pass as expected.');

        // Second request should also pass since it's a different route
        $response2 = $this->middleware->process($request2, $handler);
        $this->assertSame($responseMock, $response2, 'Second request did not pass as expected.');
    }

    /**
     * Test rate limiting for routes with wildcard.
     */
    public function testRateLimitWildcardRoute()
    {
        // Clear any existing rate limit data for this test
        //Cache::clear('rate_limit');

        $request = new ServerRequest(['url' => '/pages/add-comment']);
        $request = $request->withEnv('REMOTE_ADDR', '127.0.0.4'); // Different IP from other tests
        $handler = $this->createMock(RequestHandlerInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        // With limit of 2, only 2 requests should pass
        $handler->expects($this->exactly(2))->method('handle')->willReturn($responseMock);

        // First request should pass
        $response1 = $this->middleware->process($request, $handler);
        $this->assertSame($responseMock, $response1, 'First request did not pass as expected.');

        // Second request should pass
        $response2 = $this->middleware->process($request, $handler);
        $this->assertSame($responseMock, $response2, 'Second request did not pass as expected.');

        // Third request should throw TooManyRequestsException
        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessage('Too many requests. Please try again later.');
        $this->middleware->process($request, $handler);
    }
}
