<?php
declare(strict_types=1);

namespace App\Service;

use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * IpSecurityService handles IP-based security measures including blocking and suspicious activity detection.
 *
 * This service provides functionality to:
 * - Detect client IP addresses with proxy support
 * - Check if IP addresses are blocked
 * - Block IP addresses with optional expiration
 * - Detect suspicious requests based on URL patterns
 * - Track and respond to suspicious activity
 */
class IpSecurityService
{
    /**
     * Regular expression patterns used to identify suspicious behavior in requests.
     * Loaded from configuration.
     *
     * @var array<string>
     */
    private array $suspiciousPatterns = [];

    /**
     * @var \Cake\ORM\Table
     */
    private Table $blockedIpsTable;

    /**
     * @var array<string> List of proxy headers to check in order of preference
     */
    private array $proxyHeaders = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR', // Standard proxy header
        'HTTP_X_REAL_IP', // Nginx
        'HTTP_CLIENT_IP', // Some proxies
    ];

    /**
     * Constructor
     *
     * @param \Cake\ORM\Table|null $blockedIpsTable Table instance for BlockedIps
     */
    public function __construct(?Table $blockedIpsTable = null)
    {
        $this->blockedIpsTable = $blockedIpsTable ?? TableRegistry::getTableLocator()->get('BlockedIps');

        // Load suspicious patterns from configuration
        $this->suspiciousPatterns = Configure::read('IpSecurity.suspiciousPatterns', []);
    }

    /**
     * Get client IP address with proxy support
     *
     * @param \Cake\Http\ServerRequest $request The request
     * @return string|null The client IP address or null if cannot be determined
     */
    public function getClientIp(ServerRequest $request): ?string
    {
        // Check if IP was already determined by another middleware
        $attributeIp = $request->getAttribute('clientIp');
        if ($attributeIp && $this->isValidIp($attributeIp)) {
            return $attributeIp;
        }

        // Try CakePHP's built-in method
        if (method_exists($request, 'clientIp')) {
            $ip = $request->clientIp();
            if ($ip && $this->isValidIp($ip)) {
                return $ip;
            }
        }

        $serverParams = $request->getServerParams();

        // If trust proxy is enabled, check proxy headers
        if (SettingsManager::read('Security.trustProxy', false)) {
            $trustedProxiesConfig = SettingsManager::read('Security.trustedProxies', '');
            $trustedProxies = array_filter(
                array_map('trim', explode("\n", $trustedProxiesConfig)),
            );

            // Check if request is from a trusted proxy
            $remoteAddr = $serverParams['REMOTE_ADDR'] ?? null;

            // If trusted proxies are configured, verify the request is from one
            if (!empty($trustedProxies) && $remoteAddr) {
                if (!in_array($remoteAddr, $trustedProxies)) {
                    // Request is not from a trusted proxy, use REMOTE_ADDR
                    return $this->isValidIp($remoteAddr) ? $remoteAddr : null;
                }
            }

            // Check proxy headers in order of preference
            foreach ($this->proxyHeaders as $header) {
                if (!empty($serverParams[$header])) {
                    $ips = $this->parseIpHeader($serverParams[$header]);
                    foreach ($ips as $ip) {
                        if ($this->isValidIp($ip) && !$this->isInternalIp($ip)) {
                            return $ip;
                        }
                    }
                }
            }
        }

        // Fall back to REMOTE_ADDR
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? null;

        return $remoteAddr && $this->isValidIp($remoteAddr) ? $remoteAddr : null;
    }

    /**
     * Parse IP header which may contain multiple IPs
     *
     * @param string $header The header value to parse
     * @return array<string> Array of IP addresses
     */
    private function parseIpHeader(string $header): array
    {
        // Handle comma-separated list of IPs
        $ips = array_map('trim', explode(',', $header));

        // Return IPs in order (leftmost is usually the client)
        return $ips;
    }

    /**
     * Validate IP address
     *
     * @param string $ip The IP to validate
     * @return bool True if valid IP
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if IP is internal/private
     *
     * @param string $ip The IP to check
     * @return bool True if internal/private IP
     */
    private function isInternalIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );
    }

    /**
     * Checks if an IP address is currently blocked.
     *
     * @param string $ip The IP address to check
     * @return bool True if the IP is blocked, false otherwise
     */
    public function isIpBlocked(string $ip): bool
    {
        $cacheKey = 'blocked_ip_' . md5($ip);
        $blockedStatus = Cache::read($cacheKey, 'ip_blocker');

        if ($blockedStatus === null) {
            $blockedIp = $this->blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->where(function ($exp) {
                    return $exp->or([
                        'expires_at IS' => null,
                        'expires_at >' => FrozenTime::now(),
                    ]);
                })
                ->first();

            $blockedStatus = $blockedIp !== null;

            if ($blockedStatus && $blockedIp) {
                // Determine cache TTL based on block's expiration
                $cacheDuration = '1 day'; // Default for permanent blocks
                if ($blockedIp->expires_at) {
                    $now = new DateTimeImmutable();
                    $diffInSeconds = $blockedIp->expires_at->getTimestamp() - $now->getTimestamp();
                    if ($diffInSeconds > 0) {
                        // Cache until block expiry plus buffer
                        $cacheDuration = ($diffInSeconds + 60) . ' seconds';
                    }
                }
                Cache::write($cacheKey, true, 'ip_blocker', $cacheDuration);
            } else {
                // IP is not blocked, cache this status for a short time
                Cache::write($cacheKey, false, 'ip_blocker', '5 minutes');
            }
        }

        return (bool)$blockedStatus;
    }

    /**
     * Blocks an IP address with an optional expiration time.
     *
     * @param string $ip The IP address to block
     * @param string $reason The reason for blocking the IP
     * @param \Cake\I18n\FrozenTime|null $expiresAt Optional expiration time for the block
     * @return bool True if the block was successfully saved, false otherwise
     */
    public function blockIp(string $ip, string $reason, ?DateTimeInterface $expiresAt = null): bool
    {
        // Check for an active existing block to update
        $existing = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->where(function ($exp) {
                return $exp->or([
                    'expires_at IS' => null,
                    'expires_at >' => FrozenTime::now(),
                ]);
            })
            ->first();

        if ($existing) {
            // Update existing block
            $entity = $this->blockedIpsTable->patchEntity($existing, [
                'reason' => $reason,
                'expires_at' => $expiresAt,
            ]);
        } else {
            // Create new block
            $entity = $this->blockedIpsTable->newEntity([
                'ip_address' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt,
                'created' => FrozenTime::now(),
            ]);
        }

        if ($this->blockedIpsTable->save($entity)) {
            // Determine cache duration
            $cacheDuration = '1 day'; // Default for permanent blocks
            if ($expiresAt) {
                $now = new DateTimeImmutable();
                $diffInSeconds = $expiresAt->getTimestamp() - $now->getTimestamp();
                if ($diffInSeconds > 0) {
                    $cacheDuration = ($diffInSeconds + 60) . ' seconds';
                }
            }

            $cacheKey = 'blocked_ip_' . md5($ip);
            Cache::write($cacheKey, true, 'ip_blocker', $cacheDuration);

            Log::warning(__('IP address blocked: {0} for {1}', [$ip, $reason]), [
                'ip' => $ip,
                'reason' => $reason,
                'expires_at' => $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'never',
                'group_name' => 'security',
            ]);

            return true;
        }

        return false;
    }

    /**
     * Unblocks an IP address by removing it from the blocked list.
     *
     * @param string $ip The IP address to unblock
     * @return bool True if the IP was successfully unblocked
     */
    public function unblockIp(string $ip): bool
    {
        $blockedIp = $this->blockedIpsTable->find()
            ->where(['ip_address' => $ip])
            ->first();

        if ($blockedIp) {
            if ($this->blockedIpsTable->delete($blockedIp)) {
                Cache::delete('blocked_ip_' . md5($ip), 'ip_blocker');
                Log::info(__('IP address unblocked: {0}', [$ip]), [
                    'ip' => $ip,
                    'group_name' => 'security',
                ]);

                return true;
            }

            return false;
        } else {
            // IP was not in the blocked list
            Cache::delete('blocked_ip_' . md5($ip), 'ip_blocker');

            return true;
        }
    }

    /**
     * Checks if a request matches known suspicious patterns.
     *
     * @param \Cake\Http\ServerRequest $request The full ServerRequest object.
     * @return bool True if the request matches suspicious patterns, false otherwise
     */
    public function isSuspiciousRequest(ServerRequest $request): bool
    {
        $uri = $request->getUri();
        $fullUrl = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');
        $decodedUrl = urldecode($fullUrl);
        $doubleDecodedUrl = urldecode($decodedUrl);

        // Check all versions of the URL against patterns
        foreach ($this->suspiciousPatterns as $pattern) {
            if (
                preg_match($pattern, $fullUrl) ||
                preg_match($pattern, $decodedUrl) ||
                preg_match($pattern, $doubleDecodedUrl)
            ) {
                // Get client IP using the same method
                $clientIp = $this->getClientIp($request) ?: 'unknown';

                // Log the detected suspicious activity
                Log::warning(__('Suspicious request detected: {0}', [$fullUrl]), [
                    'pattern' => $pattern,
                    'raw_url' => $fullUrl,
                    'decoded_url' => $decodedUrl,
                    'double_decoded' => $doubleDecodedUrl,
                    'group_name' => 'security',
                    'client' => [
                        'ip_address' => $clientIp,
                        'user_agent' => $request->getHeaderLine('User-Agent') ?: 'unknown',
                        'request_method' => $request->getMethod() ?: 'unknown',
                        'referer' => $request->referer() ?: 'none',
                        'host' => $request->host() ?: 'unknown',
                    ],
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Tracks suspicious activity for an IP address and implements progressive blocking.
     *
     * @param string $ip The IP address to track
     * @param string $route The route that triggered the suspicious activity
     * @param string $query The query string that triggered the suspicious activity
     * @return void
     */
    public function trackSuspiciousActivity(string $ip, string $route, string $query): void
    {
        $key = 'suspicious_' . md5($ip);
        $data = Cache::read($key, 'ip_blocker') ?: [
            'count' => 0,
            'first_seen' => time(),
            'routes' => [],
        ];

        $data['count']++;
        $data['routes'][] = [
            'route' => $route,
            'query' => $query,
            'time' => time(),
        ];

        // Keep only the last 5 suspicious routes
        $data['routes'] = array_slice($data['routes'], -5);

        Cache::write($key, $data, 'ip_blocker');

        // Block if multiple suspicious requests within time window
        $suspiciousThreshold = (int)SettingsManager::read('Security.suspiciousRequestThreshold', 3);
        $suspiciousWindow = (int)SettingsManager::read('Security.suspiciousWindowHours', 24) * 3600;

        if ($data['count'] >= $suspiciousThreshold && (time() - $data['first_seen']) <= $suspiciousWindow) {
            $reason = __('Multiple suspicious requests detected');
            $blockHours = (int)SettingsManager::read('Security.suspiciousBlockHours', 24);
            $expiresAt = FrozenTime::now()->modify("+{$blockHours} hours");

            // Check for repeat offenders
            $previousBlock = $this->blockedIpsTable->find()
                ->where(['ip_address' => $ip])
                ->orderByDesc('created')
                ->first();

            if ($previousBlock) {
                if ($previousBlock->expires_at === null || $previousBlock->expires_at->isPast()) {
                    // Double the block time for repeat offenders
                    $blockHours *= 2;
                    $expiresAt = FrozenTime::now()->modify("+{$blockHours} hours");
                    $reason = __('Repeat offender: Multiple suspicious requests detected');
                }
            }

            $this->blockIp($ip, $reason, $expiresAt);
        }
    }
}
