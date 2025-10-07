<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Middleware\RateLimitMiddleware;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RateLimitMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Use in-memory cache for rate_limit to avoid filesystem/io and isolate tests
        Cache::setConfig('rate_limit', [
            'className' => 'Array',
            'prefix' => 'test_rl_',
            'serialize' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Cache::delete('rate_limit', 'rate_limit');
        parent::tearDown();
    }

    public function testAllowsRequestsUnderLimit(): void
    {
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 5,
            'defaultPeriod' => 60,
            'routes' => [
                '/test' => ['limit' => 5, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => '/test']))
            ->withAttribute('clientIp', '127.0.0.1');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        // 5 requests should pass
        for ($i = 0; $i < 5; $i++) {
            $response = $middleware->process($request, $handler);
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    public function testBlocksWhenLimitExceeded(): void
    {
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 2,
            'defaultPeriod' => 60,
            'routes' => [
                '/test' => ['limit' => 2, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => '/test']))
            ->withAttribute('clientIp', '192.168.0.1');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        // First 2 pass
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);

        // Third should throw
        $this->expectException(TooManyRequestsException::class);
        $middleware->process($request, $handler);
    }

    public function testDisabledByConfigSkipsLimiting(): void
    {
        $middleware = new RateLimitMiddleware([
            'enabled' => false,
        ]);

        $request = (new ServerRequest(['url' => '/anything']))
            ->withAttribute('clientIp', '10.0.0.1');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        // Should never throw, even if called many times
        for ($i = 0; $i < 10; $i++) {
            $response = $middleware->process($request, $handler);
            $this->assertSame(200, $response->getStatusCode());
        }
    }
}
