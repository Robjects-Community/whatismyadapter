<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\IpBlockerMiddleware;
use App\Service\IpSecurityService;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * IpBlockerMiddleware Test Case
 *
 * Tests IP detection, blocking, suspicious request detection, and response handling
 */
class IpBlockerMiddlewareTest extends TestCase
{
    /**
     * Test that requests from blocked IPs are rejected
     *
     * @return void
     */
    public function testBlocksRequestsFromBlockedIps(): void
    {
        // Mock IpSecurityService
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->with('192.168.1.100')
            ->willReturn(true);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Access Denied', (string)$response->getBody());
    }

    /**
     * Test that requests from non-blocked IPs are allowed
     *
     * @return void
     */
    public function testAllowsRequestsFromNonBlockedIps(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->with('192.168.1.100')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(false);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that suspicious requests are detected and blocked
     *
     * @return void
     */
    public function testBlocksSuspiciousRequests(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(true);
        $ipService->expects($this->once())
            ->method('trackSuspiciousActivity')
            ->with('192.168.1.100', '/test', '');

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Suspicious request detected', (string)$response->getBody());
    }

    /**
     * Test that clientIp attribute is set on request
     *
     * @return void
     */
    public function testSetsClientIpAttribute(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(false);

        $middleware = new IpBlockerMiddleware($ipService);

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

        $request = new ServerRequest(['url' => '/test']);
        $middleware->process($request, $handler);

        $this->assertSame('192.168.1.100', $capturedRequest->getAttribute('clientIp'));
    }

    /**
     * Test response format for JSON requests
     *
     * @return void
     */
    public function testReturnsJsonResponseForJsonRequests(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(true);

        // Simulate JSON Accept header
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/api/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertArrayHasKey('code', $body);

        unset($_SERVER['HTTP_ACCEPT']);
    }

    /**
     * Test security headers are set in blocked responses
     *
     * @return void
     */
    public function testSetsSecurityHeadersInBlockedResponses(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(true);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->getHeaderLine('X-Frame-Options'));
        $this->assertSame('1; mode=block', $response->getHeaderLine('X-XSS-Protection'));
    }

    /**
     * Test behavior when IP cannot be determined and blockOnNoIp is disabled
     *
     * Note: In test environment, SettingsManager::read() returns the default value.
     * The middleware uses SettingsManager::read('Security.blockOnNoIp', true) which
     * returns true by default in tests, so we cannot easily mock this behavior.
     * This test documents the expected production behavior.
     *
     * @return void
     */
    public function testAllowsRequestWhenIpCannotBeDeterminedAndBlockingDisabled(): void
    {
        $this->markTestSkipped(
            'SettingsManager returns default values in test environment. ' .
            'This behavior is tested via integration tests with actual Settings records.'
        );

        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn(null);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test behavior when IP cannot be determined and blockOnNoIp is enabled
     *
     * @return void
     */
    public function testBlocksRequestWhenIpCannotBeDeterminedAndBlockingEnabled(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn(null);

        // Mock Configure to enable blockOnNoIp
        \Cake\Core\Configure::write('Security.blockOnNoIp', true);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Unable to verify request origin', (string)$response->getBody());

        // Clean up
        \Cake\Core\Configure::delete('Security.blockOnNoIp');
    }

    /**
     * Test suspicious request tracking with query parameters
     *
     * @return void
     */
    public function testTracksSuspiciousActivityWithQueryParameters(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(true);
        $ipService->expects($this->once())
            ->method('trackSuspiciousActivity')
            ->with('192.168.1.100', '/test', 'foo=bar&baz=qux');

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest([
            'url' => '/test?foo=bar&baz=qux',
        ]);
        $handler = $this->createMockHandler();

        $middleware->process($request, $handler);
    }

    /**
     * Test that middleware passes request to next handler when checks pass
     *
     * @return void
     */
    public function testPassesRequestToNextHandlerWhenChecksPass(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('192.168.1.100');
        $ipService->method('isIpBlocked')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(false);

        $middleware = new IpBlockerMiddleware($ipService);

        $handlerCalled = false;
        $handler = new class ($handlerCalled) implements RequestHandlerInterface {
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

        $request = new ServerRequest(['url' => '/test']);
        $middleware->process($request, $handler);

        $this->assertTrue($handlerCalled);
    }

    /**
     * Test IPv6 address handling
     *
     * @return void
     */
    public function testHandlesIpv6Addresses(): void
    {
        $ipService = $this->createMock(IpSecurityService::class);
        $ipService->method('getClientIp')
            ->willReturn('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $ipService->method('isIpBlocked')
            ->with('2001:0db8:85a3:0000:0000:8a2e:0370:7334')
            ->willReturn(false);
        $ipService->method('isSuspiciousRequest')
            ->willReturn(false);

        $middleware = new IpBlockerMiddleware($ipService);

        $request = new ServerRequest(['url' => '/test']);
        $handler = $this->createMockHandler();

        $response = $middleware->process($request, $handler);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Create a mock request handler
     *
     * @return \Psr\Http\Server\RequestHandlerInterface
     */
    private function createMockHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }
}
