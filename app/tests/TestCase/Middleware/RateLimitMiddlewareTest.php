<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Http\Exception\TooManyRequestsException;
use App\Middleware\RateLimitMiddleware;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddlewareTest extends TestCase
{
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
            ->withAttribute('clientIp', '********');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

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
            ->withAttribute('clientIp', '********');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);

        $this->expectException(TooManyRequestsException::class);
        $middleware->process($request, $handler);
    }

    public function testDisabledByConfigSkipsLimiting(): void
    {
        $middleware = new RateLimitMiddleware([
            'enabled' => false,
        ]);

        $request = (new ServerRequest(['url' => '/anything']))
            ->withAttribute('clientIp', '********');
        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        for ($i = 0; $i < 10; $i++) {
            $response = $middleware->process($request, $handler);
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    public function testWildcardAdminRouteMatchesAndLimits(): void
    {
        if (method_exists(Cache::class, 'clear')) { Cache::clear('rate_limit'); }
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 100,
            'defaultPeriod' => 60,
            'routes' => [
                '/admin/*' => ['limit' => 1, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => '/admin/users']))
            ->withAttribute('clientIp', '*********');

        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = $middleware->process($request, $handler);
        $this->assertSame(200, $response->getStatusCode());

        $this->expectException(TooManyRequestsException::class);
        $middleware->process($request, $handler);
    }

    public function testWildcardWithLanguagePrefixMatches(): void
    {
        if (method_exists(Cache::class, 'clear')) { Cache::clear('rate_limit'); }
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'defaultLimit' => 100,
            'defaultPeriod' => 60,
            'routes' => [
                '/admin/*' => ['limit' => 1, 'period' => 60],
            ],
        ]);

        $request = (new ServerRequest(['url' => '/en/admin/dashboard']))
            ->withAttribute('clientIp', '*********');

        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = $middleware->process($request, $handler);
        $this->assertSame(200, $response->getStatusCode());

        $this->expectException(TooManyRequestsException::class);
        $middleware->process($request, $handler);
    }

    public function testPerIpTrackingSeparatesLimits(): void
    {
        if (method_exists(Cache::class, 'clear')) { Cache::clear('rate_limit'); }
        $route = '/admin/settings';
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'routes' => [
                $route => ['limit' => 1, 'period' => 60],
            ],
        ]);

        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $reqA = (new ServerRequest(['url' => $route]))->withAttribute('clientIp', '************');
        $reqB = (new ServerRequest(['url' => $route]))->withAttribute('clientIp', '*************');

        $this->assertSame(200, $middleware->process($reqA, $handler)->getStatusCode());
        $this->assertSame(200, $middleware->process($reqB, $handler)->getStatusCode());

        $this->expectException(TooManyRequestsException::class);
        $middleware->process($reqA, $handler);
    }

    public function testDifferentRoutesUseDifferentKeys(): void
    {
        if (method_exists(Cache::class, 'clear')) { Cache::clear('rate_limit'); }
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'routes' => [
                '/admin/users' => ['limit' => 1, 'period' => 60],
                '/admin/settings' => ['limit' => 1, 'period' => 60],
            ],
        ]);

        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $reqUsers = (new ServerRequest(['url' => '/admin/users']))->withAttribute('clientIp', '*********');
        $reqSettings = (new ServerRequest(['url' => '/admin/settings']))->withAttribute('clientIp', '*********');

        $this->assertSame(200, $middleware->process($reqUsers, $handler)->getStatusCode());
        $this->assertSame(200, $middleware->process($reqSettings, $handler)->getStatusCode());
    }

    public function testClientIpAttributeOverridesProxyHeaders(): void
    {
        if (method_exists(Cache::class, 'clear')) { Cache::clear('rate_limit'); }
        $middleware = new RateLimitMiddleware([
            'enabled' => true,
            'routes' => [
                '/admin/users' => ['limit' => 1, 'period' => 60],
            ],
        ]);

        $server = [
            'HTTP_X_FORWARDED_FOR' => '*************',
            'REMOTE_ADDR' => '*********',
        ];
        $request = (new ServerRequest(['url' => '/admin/users', 'environment' => $server]))
            ->withAttribute('clientIp', '*************');

        $handler = new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $this->assertSame(200, $middleware->process($request, $handler)->getStatusCode());

        $this->expectException(TooManyRequestsException::class);
        $middleware->process($request, $handler);
    }
}
