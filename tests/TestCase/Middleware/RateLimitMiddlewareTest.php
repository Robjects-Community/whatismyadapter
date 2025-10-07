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
    // No special setup/teardown required for cache in these starter tests

    public function testAllowsRequestsUnderLimit(): void
    {
        $uniqueRoute = '/test-' . substr(md5((string)microtime(true)), 0, 8);
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 5,
            'defaultPeriod' => 60,
            'routes' => [
                $uniqueRoute => ['limit' => 5, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => $uniqueRoute]))
            ->withAttribute('clientIp', '10.0.0.5');
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
        $uniqueRoute = '/test-' . substr(md5((string)microtime(true)), 0, 8);
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 2,
            'defaultPeriod' => 60,
            'routes' => [
                $uniqueRoute => ['limit' => 2, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => $uniqueRoute]))
            ->withAttribute('clientIp', '10.0.0.6');
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
            ->withAttribute('clientIp', '10.0.0.7');
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
