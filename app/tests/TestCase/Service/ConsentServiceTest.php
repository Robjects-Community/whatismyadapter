<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ConsentService;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * ConsentService Test Case
 *
 * Tests the ConsentService class functionality including session management,
 * cookie handling, and error scenarios.
 *
 * @uses \App\Service\ConsentService
 */
class ConsentServiceTest extends TestCase
{
    /**
     * ConsentService instance
     *
     * @var \App\Service\ConsentService
     */
    protected ConsentService $service;

    /**
     * Setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ConsentService();
    }

    /**
     * Test getConsentData with valid cookie and started session
     *
     * @return void
     */
    public function testGetConsentDataWithValidCookie(): void
    {
        $consentData = [
            'analytics' => true,
            'functional' => true,
            'marketing' => false,
            'essential' => true,
        ];

        $session = $this->createMockSession(true, 'test-session-id');
        $request = $this->createMockRequest($session, json_encode($consentData));

        $result = $this->service->getConsentData($request);

        $this->assertEquals('test-session-id', $result['sessionId']);
        $this->assertEquals($consentData, $result['consentData']);
    }

    /**
     * Test getConsentData with no cookie
     *
     * @return void
     */
    public function testGetConsentDataWithNoCookie(): void
    {
        $session = $this->createMockSession(true, 'test-session-id');
        $request = $this->createMockRequest($session, null);

        $result = $this->service->getConsentData($request);

        $this->assertEquals('test-session-id', $result['sessionId']);
        $this->assertNull($result['consentData']);
    }

    /**
     * Test getConsentData with session that needs starting
     *
     * @return void
     */
    public function testGetConsentDataWithSessionNeedsStarting(): void
    {
        $session = $this->createMockSession(false, 'new-session-id');
        $session->expects($this->once())
                ->method('start')
                ->willReturn(true);

        $request = $this->createMockRequest($session, null);

        $result = $this->service->getConsentData($request);

        $this->assertEquals('new-session-id', $result['sessionId']);
        $this->assertNull($result['consentData']);
    }

    /**
     * Test handleConsentCookie with valid JSON
     *
     * @return void
     */
    public function testHandleConsentCookieWithValidJson(): void
    {
        $consentData = ['analytics' => true, 'functional' => false];
        $request = $this->createMockRequest(null, json_encode($consentData));

        $result = $this->service->handleConsentCookie($request);

        $this->assertEquals($consentData, $result);
    }

    /**
     * Test handleConsentCookie with no cookie
     *
     * @return void
     */
    public function testHandleConsentCookieWithNoCookie(): void
    {
        $request = $this->createMockRequest(null, null);

        $result = $this->service->handleConsentCookie($request);

        $this->assertNull($result);
    }

    /**
     * Test handleConsentCookie with invalid JSON
     *
     * @return void
     */
    public function testHandleConsentCookieWithInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid consent cookie data:');

        $request = $this->createMockRequest(null, 'invalid-json-{');

        $this->service->handleConsentCookie($request);
    }

    /**
     * Test startSessionIfNeeded with already started session
     *
     * @return void
     */
    public function testStartSessionIfNeededWithStartedSession(): void
    {
        $session = $this->createMockSession(true, 'existing-session-id');
        $session->expects($this->never())
                ->method('start');

        $request = $this->createMockRequest($session);

        $result = $this->service->startSessionIfNeeded($request);

        $this->assertEquals('existing-session-id', $result);
    }

    /**
     * Test startSessionIfNeeded with session that needs starting
     *
     * @return void
     */
    public function testStartSessionIfNeededWithUnstartedSession(): void
    {
        $session = $this->createMockSession(false, 'new-session-id');
        $session->expects($this->once())
                ->method('start')
                ->willReturn(true);

        $request = $this->createMockRequest($session);

        $result = $this->service->startSessionIfNeeded($request);

        $this->assertEquals('new-session-id', $result);
    }

    /**
     * Test startSessionIfNeeded with session start failure
     *
     * @return void
     */
    public function testStartSessionIfNeededWithStartFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to start session for consent tracking');

        $session = $this->createMockSession(false, 'session-id');
        $session->expects($this->once())
                ->method('start')
                ->willReturn(false);

        $request = $this->createMockRequest($session);

        $this->service->startSessionIfNeeded($request);
    }

    /**
     * Test handleConsentCookie with empty string cookie
     *
     * @return void
     */
    public function testHandleConsentCookieWithEmptyString(): void
    {
        $request = $this->createMockRequest(null, '');

        $result = $this->service->handleConsentCookie($request);

        $this->assertNull($result);
    }

    /**
     * Test getConsentData with complex cookie data
     *
     * @return void
     */
    public function testGetConsentDataWithComplexCookieData(): void
    {
        $complexConsentData = [
            'analytics' => true,
            'functional' => false,
            'marketing' => true,
            'essential' => true,
            'timestamp' => '2025-01-06T12:00:00Z',
            'version' => '2.1',
            'preferences' => [
                'language' => 'en',
                'theme' => 'dark',
            ],
        ];

        $session = $this->createMockSession(true, 'complex-session-id');
        $request = $this->createMockRequest($session, json_encode($complexConsentData));

        $result = $this->service->getConsentData($request);

        $this->assertEquals('complex-session-id', $result['sessionId']);
        $this->assertEquals($complexConsentData, $result['consentData']);
        $this->assertIsArray($result['consentData']['preferences']);
    }

    /**
     * Create a mock Session object
     *
     * @param bool $started Whether the session is started
     * @param string $sessionId The session ID to return
     * @return \PHPUnit\Framework\MockObject\MockObject|\Cake\Http\Session
     */
    private function createMockSession(bool $started, string $sessionId): MockObject
    {
        $session = $this->createMock(Session::class);
        $session->method('started')->willReturn($started);
        $session->method('id')->willReturn($sessionId);

        return $session;
    }

    /**
     * Create a mock ServerRequest object
     *
     * @param \PHPUnit\Framework\MockObject\MockObject|\Cake\Http\Session|null $session Session mock
     * @param string|null $cookieValue The consent cookie value
     * @return \PHPUnit\Framework\MockObject\MockObject|\Cake\Http\ServerRequest
     */
    private function createMockRequest(?MockObject $session, ?string $cookieValue = null): MockObject
    {
        $request = $this->createMock(ServerRequest::class);

        if ($session !== null) {
            $request->method('getSession')->willReturn($session);
        }

        $request->method('getCookie')
                ->with('consent_cookie')
                ->willReturn($cookieValue);

        return $request;
    }
}
