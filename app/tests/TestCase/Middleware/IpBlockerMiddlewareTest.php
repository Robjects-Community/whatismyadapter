<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Middleware\IpBlockerMiddleware;
use App\Test\Traits\SecurityMiddlewareTestTrait;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\Uri;
use Psr\Http\Server\RequestHandlerInterface;

class IpBlockerMiddlewareTest extends TestCase
{
    use SecurityMiddlewareTestTrait;

    protected $middleware;
    protected $blockedIpsTable;

    public array $fixtures = [
        'app.BlockedIps',
        'app.Settings',
    ];

    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Enable security middleware for these tests
        $this->enableSecurityMiddleware();

        $this->middleware = new IpBlockerMiddleware();
        $this->blockedIpsTable = TableRegistry::getTableLocator()->get('BlockedIps');
        Cache::clear('ip_blocker');
    }

    /**
     * Clean up after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Disable security middleware after tests
        $this->disableSecurityMiddleware();

        Cache::clear('ip_blocker');
        $this->getTableLocator()->clear();
    }

    /**
     * Test the process method with a blocked IP address.
     *
     * This test ensures that when a request comes from a blocked IP address,
     * the middleware returns a 403 response with the correct error message.
     *
     * @return void
     */
    public function testProcessWithBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.1']]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->blockedIpsTable->save($this->blockedIpsTable->newEntity([
            'ip_address' => '192.0.2.1',
            'reason' => 'Test blocking',
            'created' => new DateTime(),
        ]));

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Your IP address has been blocked due to suspicious activity.', (string)$response->getBody());
    }

    /**
     * Test the process method with a non-blocked IP address.
     *
     * This test verifies that when a request comes from a non-blocked IP address,
     * the middleware allows the request to pass through to the next handler,
     * resulting in a 200 response.
     *
     * @return void
     */
    public function testProcessWithNonBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.2']]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test the process method with a cached blocked status.
     *
     * This test ensures that when a blocked IP status is cached,
     * the middleware returns a 403 response without querying the database.
     *
     * @return void
     */
    public function testProcessWithCachedBlockedStatus(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.3']]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        // Use MD5 hash of IP for cache key (matching the service)
        $cacheKey = 'blocked_ip_' . md5('192.0.2.3');
        Cache::write($cacheKey, true, 'ip_blocker');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test the process method with an expired blocked IP address.
     *
     * This test verifies that when a request comes from an IP address
     * that was blocked but the block has expired, the middleware
     * allows the request to pass through to the next handler.
     *
     * @return void
     */
    public function testProcessWithExpiredBlockedIp(): void
    {
        $request = new ServerRequest(['environment' => ['REMOTE_ADDR' => '192.0.2.4']]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $this->blockedIpsTable->save($this->blockedIpsTable->newEntity([
            'ip_address' => '192.0.2.4',
            'reason' => 'Test blocking',
            'created' => new DateTime(),
            'expires_at' => (new DateTime())->modify('-1 day'),
        ]));

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test the process method when the REMOTE_ADDR is missing and blockOnNoIp is true.
     *
     * @return void
     */
    public function testProcessWithMissingRemoteAddrBlocked(): void
    {
        // Ensure blockOnNoIp is true (the default)
        SettingsManager::write('Security.blockOnNoIp', true);

        $request = new ServerRequest();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Access Denied: Unable to verify request origin.', (string)$response->getBody());
    }

    /**
     * Test the process method when the REMOTE_ADDR is missing and blockOnNoIp is false.
     *
     * @return void
     */
    public function testProcessWithMissingRemoteAddrAllowed(): void
    {
        // Set blockOnNoIp to false
        $originalValue = SettingsManager::read('Security.blockOnNoIp');
        SettingsManager::write('Security.blockOnNoIp', false);

        try {
            $request = new ServerRequest();
            $handler = $this->createMock(RequestHandlerInterface::class);
            $handler->expects($this->once())
                ->method('handle')
                ->willReturn(new Response());

            $response = $this->middleware->process($request, $handler);

            $this->assertEquals(200, $response->getStatusCode());
        } finally {
            // Restore original value
            if ($originalValue !== null) {
                SettingsManager::write('Security.blockOnNoIp', $originalValue);
            } else {
                SettingsManager::delete('Security.blockOnNoIp');
            }
        }
    }

    /**
     * Test the process method with a suspicious request pattern.
     *
     * @return void
     */
    public function testProcessWithSuspiciousRequest(): void
    {
        $uri = new Uri('/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.5'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Test the process method with URL encoded suspicious pattern.
     *
     * @return void
     */
    public function testProcessWithEncodedSuspiciousRequest(): void
    {
        $uri = new Uri('/%2e%2e/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.6'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Test multiple suspicious requests leading to IP block.
     *
     * @return void
     */
    public function testMultipleSuspiciousRequestsLeadToBlock(): void
    {
        $ip = '192.0.2.7';
        $uri = new Uri('/etc/passwd');
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        // The default threshold is 3, so we need at least 3 suspicious requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->middleware->process($request, $handler);
            $this->assertEquals(403, $response->getStatusCode());
        }

        // Verify IP is now blocked in database
        $blocked = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->first();

        $this->assertNotNull($blocked);
        $this->assertNotNull($blocked->expires_at); // Should have an expiration for trackSuspiciousActivity
    }

    /**
     * Test path traversal detection.
     *
     * @dataProvider pathTraversalProvider
     * @param string $path The path to test
     * @return void
     */
    public function testPathTraversalDetection(string $path): void
    {
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.10'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for path traversal tests.
     *
     * @return array<array<string>>
     */
    public static function pathTraversalProvider(): array
    {
        return [
            ['/../etc/passwd'],
            ['/%2e%2e/etc/passwd'],
            ['/%252e%252e/etc/passwd'],
            ['/%c0%ae%c0%ae/etc/passwd'],
            ['/images/../../etc/passwd'],
            ['/theme/%2e%2e/%2e%2e/etc/shadow'],
        ];
    }

    /**
     * Test SQL injection detection.
     *
     * @dataProvider sqlInjectionProvider
     * @param string $query The query string to test
     * @return void
     */
    public function testSqlInjectionDetection(string $query): void
    {
        $uri = new Uri('/search?query=' . urlencode($query));
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.11'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for SQL injection tests.
     *
     * @return array<array<string>>
     */
    public static function sqlInjectionProvider(): array
    {
        return [
            ['UNION SELECT username,password FROM users'],
            ['SELECT * FROM users WHERE id = 1'],
            ['INSERT INTO users (username,password) VALUES ("admin","hack")'],
            ['UPDATE users SET password="hacked" WHERE id=1'],
            ['DELETE FROM users WHERE id=1'],
            ['1; DROP TABLE users'],
        ];
    }

    /**
     * Test XSS detection.
     *
     * @dataProvider xssProvider
     * @param string $input The potentially malicious input
     * @return void
     */
    public function testXssDetection(string $input): void
    {
        $uri = new Uri('/profile?bio=' . urlencode($input));
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.12'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Access Denied: Suspicious request detected.', (string)$response->getBody());
    }

    /**
     * Data provider for XSS tests.
     *
     * @return array<array<string>>
     */
    public static function xssProvider(): array
    {
        return [
            ['<script>alert("xss")</script>'],
            ['<iframe src="evil.com"></iframe>'],
            ['javascript:alert("xss")'],
            ['<img src="x" onerror="alert(\'xss\')">'],
            ['<body onload="alert(\'xss\')">'],
            ['<svg/onload=alert(1)>'],
            ['"><script>alert(document.cookie)</script>'],
        ];
    }

    /**
     * Test that legitimate requests are not blocked.
     *
     * @dataProvider legitimateRequestProvider
     * @param string $path The path to test
     * @return void
     */
    public function testLegitimateRequests(string $path): void
    {
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => '192.0.2.13'],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Data provider for legitimate request tests.
     *
     * @return array<array<string>>
     */
    public static function legitimateRequestProvider(): array
    {
        return [
            ['/articles/view/1'],
            ['/users/profile'],
            ['/images/photo.jpg'],
            ['/blog/2024/01/my-first-post'],
            ['/search?q=normal+search+term'],
            ['/contact?message=Hello+there'],
        ];
    }

    // --- NEW TESTS ARE BELOW ---

    /**
     * Test for specific false positives for command injection patterns.
     * Ensures that legitimate words containing parts of command injection patterns are not blocked.
     *
     * @dataProvider falsePositiveCommandInjectionProvider
     * @param string $path The path containing the potentially false positive string
     * @return void
     */
    public function testFalsePositiveCommandInjection(string $path): void
    {
        $ip = '192.0.2.14'; // Use a distinct IP for this test
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());

        // Also assert that the IP was *not* marked as suspicious in cache, which would lead to a block
        $cacheKey = 'suspicious_' . md5($ip);
        $this->assertNotSame(true, Cache::read($cacheKey, 'ip_blocker'));
    }

    /**
     * Data provider for false positive command injection tests.
     *
     * @return array<array<string>>
     */
    public static function falsePositiveCommandInjectionProvider(): array
    {
        return [
            ['/search?q=normal+search+term'], // Already in legitimate, but good to emphasize
            ['/contact?message=Could you clarify the term?'],
            ['/admin/reports/performance'], // Contains 'or' and 'rm' but should be fine
            ['/users/confirm/email'], // Contains 'rm'
            ['/api/platform/image-upload'], // Contains 'rm' and 'chown'
            ['/api/system/monitor'], // Contains 'rm'
            ['/items/format/json'], // Contains 'rm'
            ['/help/chown-me'], // Literal test for 'chown' being part of legitimate path name
        ];
    }

    /**
     * Test for legitimate URLs that look like file extensions caught by suspicious patterns.
     *
     * @dataProvider falsePositiveFileExtensionProvider
     * @param string $path
     * @return void
     */
    public function testFalsePositiveFileExtension(string $path): void
    {
        $ip = '192.0.2.15';
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $cacheKey = 'suspicious_' . md5($ip);
        $this->assertNotSame(true, Cache::read($cacheKey, 'ip_blocker'));
    }

    /**
     * Data provider for false positive file extension tests.
     *
     * @return array<array<string>>
     */
    public static function falsePositiveFileExtensionProvider(): array
    {
        return [
            ['/articles/phtml-content'], // Contains phtml as part of a word
            ['/videos/asphalt'], // Contains asp as a word
            ['/images/php-logo.png'], // PHP in filename, but not an executable extension
            ['/downloads/latest.zip?file=my.php.document'], // .php.document should not match .php$
            ['/users/jason/profile.json'], // ending with .json
            ['/app/configs/my.yaml'], // ending with .yaml
            ['/data/logs/access.log'], // ending with .log
            ['/backup/archive.tar.gz'], // legit tar.gz
        ];
    }

    /**
     * Test for legitimate URLs that contain parts of XSS patterns.
     *
     * @dataProvider falsePositiveXssProvider
     * @param string $path
     * @return void
     */
    public function testFalsePositiveXss(string $path): void
    {
        $ip = '192.0.2.16';
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $cacheKey = 'suspicious_' . md5($ip);
        $this->assertNotSame(true, Cache::read($cacheKey, 'ip_blocker'));
    }

    /**
     * Data provider for false positive XSS tests.
     *
     * @return array<array<string>>
     */
    public static function falsePositiveXssProvider(): array
    {
        return [
            ['/contact?message=I use javascript for validation.'],
            ['/products?description=Contains interesting script for you!'],
            ['/admin/config?setting=security.script_timeout_ms'],
            ['/user/profile?bio=My favorite programming language is javascript.'],
            ['/blog/post?title=A Comprehensive Guide to IFRAME Elements'], // Legitimate use of "iframe" word
        ];
    }

    /**
     * Test for legitimate URLs that contain parts of SQL injection patterns.
     *
     * @dataProvider falsePositiveSqlProvider
     * @param string $path
     * @return void
     */
    public function testFalsePositiveSql(string $path): void
    {
        $ip = '192.0.2.17';
        $uri = new Uri($path);
        $request = new ServerRequest([
            'environment' => ['REMOTE_ADDR' => $ip],
            'uri' => $uri,
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
        $cacheKey = 'suspicious_' . md5($ip);
        $this->assertNotSame(true, Cache::read($cacheKey, 'ip_blocker'));
    }

    /**
     * Data provider for false positive SQL tests.
     *
     * @return array<array<string>>
     */
    public static function falsePositiveSqlProvider(): array
    {
        return [
            ['/articles/section/union-street-market'], // "union" and "select" as part of street name
            ['/products?sort=select_best_selling'], // "select" as part of a param value
            ['/orders/status?filter=new-insertions'], // "insert" as part of a word
            ['/documentation/update-procedures'], // "update" as part of a word
            ['/users/delete-account-instructions'], // "delete" as part of a word
            ['/database/drop-down-options'], // "drop" as part of a word
            ['/search?term=select+language'], // "select" as a verb in search term
            ['/articles/union-of-states-history'],
        ];
    }
}
