<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\CookieConsent;
use App\Model\Table\CookieConsentsTable;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CookieConsentsTable Test Case
 *
 * Comprehensive test suite for CookieConsentsTable including:
 * - Initialization and configuration tests
 * - Validation tests for all fields
 * - Business rules validation
 * - Cookie creation functionality
 * - Latest consent retrieval logic
 */
class CookieConsentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CookieConsentsTable
     */
    protected $CookieConsents;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.CookieConsents',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CookieConsents') ? [] : ['className' => CookieConsentsTable::class];
        $this->CookieConsents = $this->getTableLocator()->get('CookieConsents', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CookieConsents);

        parent::tearDown();
    }

    // ============================================================
    // Initialization Tests
    // ============================================================

    /**
     * Test initialize method sets up table correctly
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('cookie_consents', $this->CookieConsents->getTable(), 'Table name should be cookie_consents');
        $this->assertEquals('ip_address', $this->CookieConsents->getDisplayField(), 'Display field should be ip_address');
        $this->assertEquals('id', $this->CookieConsents->getPrimaryKey(), 'Primary key should be id');
        
        // Test behaviors are attached
        $this->assertTrue($this->CookieConsents->hasBehavior('Timestamp'), 'Timestamp behavior should be loaded');
        
        // Test associations
        $this->assertTrue($this->CookieConsents->hasAssociation('Users'), 'Users association should exist');
        $association = $this->CookieConsents->getAssociation('Users');
        $this->assertEquals('user_id', $association->getForeignKey(), 'Foreign key should be user_id');
    }

    // ============================================================
    // Validation Tests
    // ============================================================

    /**
     * Test validationDefault with valid data
     *
     * @return void
     */
    public function testValidationDefaultSuccess(): void
    {
        $data = [
            'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
            'session_id' => 'test_session',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertEmpty($consent->getErrors(), 'Expected no validation errors for valid data');
    }

    /**
     * Test ip_address is required
     *
     * @return void
     */
    public function testValidationIpAddressRequired(): void
    {
        $data = [
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertNotEmpty($consent->getError('ip_address'), 'ip_address should be required');
    }

    /**
     * Test ip_address max length validation
     *
     * @return void
     */
    public function testValidationIpAddressMaxLength(): void
    {
        $data = [
            'ip_address' => str_repeat('a', 46), // Exceeds 45 char limit
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertNotEmpty($consent->getError('ip_address'), 'ip_address should fail max length validation');
    }

    /**
     * Test user_agent is required
     *
     * @return void
     */
    public function testValidationUserAgentRequired(): void
    {
        $data = [
            'ip_address' => '192.168.1.1',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertNotEmpty($consent->getError('user_agent'), 'user_agent should be required');
    }

    /**
     * Test user_agent max length validation
     *
     * @return void
     */
    public function testValidationUserAgentMaxLength(): void
    {
        $data = [
            'ip_address' => '192.168.1.1',
            'user_agent' => str_repeat('a', 256), // Exceeds 255 char limit
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertNotEmpty($consent->getError('user_agent'), 'user_agent should fail max length validation');
    }

    /**
     * Test session_id allows empty
     *
     * @return void
     */
    public function testValidationSessionIdAllowsEmpty(): void
    {
        $data = [
            'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertEmpty($consent->getError('session_id'), 'session_id should allow empty value');
    }

    /**
     * Test session_id max length validation
     *
     * @return void
     */
    public function testValidationSessionIdMaxLength(): void
    {
        $data = [
            'session_id' => str_repeat('a', 256), // Exceeds 255 char limit
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertNotEmpty($consent->getError('session_id'), 'session_id should fail max length validation');
    }

    /**
     * Test user_id accepts valid UUID
     *
     * @return void
     */
    public function testValidationUserIdAcceptsValidUuid(): void
    {
        $data = [
            'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertEmpty($consent->getError('user_id'), 'user_id should accept valid UUID');
    }

    /**
     * Test user_id allows empty
     *
     * @return void
     */
    public function testValidationUserIdAllowsEmpty(): void
    {
        $data = [
            'session_id' => 'test_session',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->assertEmpty($consent->getError('user_id'), 'user_id should allow empty value');
    }

    /**
     * Test consent fields require boolean values
     *
     * @return void
     */
    public function testValidationConsentFieldsRequireBoolean(): void
    {
        $consentFields = ['analytics_consent', 'functional_consent', 'marketing_consent', 'essential_consent'];
        
        foreach ($consentFields as $field) {
            $data = [
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'analytics_consent' => true,
                'functional_consent' => true,
                'marketing_consent' => true,
                'essential_consent' => true,
                $field => 'not_a_boolean', // Invalid value
            ];
            
            $consent = $this->CookieConsents->newEntity($data);
            $this->assertNotEmpty(
                $consent->getError($field),
                "Field '{$field}' should require boolean value"
            );
        }
    }

    // ============================================================
    // Build Rules Tests
    // ============================================================

    /**
     * Test valid user_id passes existsIn rule
     *
     * @return void
     */
    public function testBuildRulesValidUserIdPasses(): void
    {
        $data = [
            'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449', // Valid user from fixture
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $result = $this->CookieConsents->save($consent);
        $this->assertInstanceOf(CookieConsent::class, $result, 'Entity with valid user_id should save successfully');
    }

    /**
     * Test invalid user_id fails existsIn rule
     *
     * @return void
     */
    public function testBuildRulesInvalidUserIdFails(): void
    {
        $data = [
            'user_id' => '00000000-0000-0000-0000-000000000000', // Non-existent user
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $result = $this->CookieConsents->save($consent);
        $this->assertFalse($result, 'Entity with invalid user_id should fail to save');
        $this->assertNotEmpty($consent->getError('user_id'), 'user_id should have error for non-existent user');
    }

    /**
     * Test null user_id is allowed
     *
     * @return void
     */
    public function testBuildRulesNullUserIdAllowed(): void
    {
        $data = [
            'user_id' => null,
            'session_id' => 'test_session',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $result = $this->CookieConsents->save($consent);
        $this->assertInstanceOf(CookieConsent::class, $result, 'Entity with null user_id should save successfully');
    }

    // ============================================================
    // Cookie Creation Tests
    // ============================================================

    /**
     * Test createConsentCookie creates cookie with correct properties
     *
     * @return void
     */
    public function testCreateConsentCookie(): void
    {
        $consentData = [
            'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
        ];
        
        $consent = new CookieConsent($consentData);
        $cookie = $this->CookieConsents->createConsentCookie($consent);
        
        // Test cookie name
        $this->assertEquals('consent_cookie', $cookie->getName(), 'Cookie name should be consent_cookie');
        
        // Test cookie value contains correct JSON
        $value = json_decode($cookie->getValue(), true);
        $this->assertIsArray($value, 'Cookie value should be valid JSON');
        $this->assertEquals($consentData['user_id'], $value['user_id'], 'Cookie should contain user_id');
        $this->assertTrue($value['analytics_consent'], 'Cookie should contain analytics_consent');
        $this->assertTrue($value['functional_consent'], 'Cookie should contain functional_consent');
        $this->assertFalse($value['marketing_consent'], 'Cookie should contain marketing_consent');
        $this->assertTrue($value['essential_consent'], 'Cookie should always set essential_consent to true');
        $this->assertArrayHasKey('created', $value, 'Cookie should contain created timestamp');
        
        // Test cookie configuration
        $this->assertEquals('/', $cookie->getPath(), 'Cookie path should be /');
        $this->assertTrue($cookie->isHttpOnly(), 'Cookie should be HttpOnly');
        $this->assertEquals('Lax', $cookie->getSameSite(), 'Cookie SameSite should be Lax');
    }

    /**
     * Test createConsentCookie sets Secure flag when HTTPS is on
     *
     * @return void
     */
    public function testCreateConsentCookieSecureFlagWithHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';
        
        $consent = new CookieConsent([
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
        ]);
        
        $cookie = $this->CookieConsents->createConsentCookie($consent);
        $this->assertTrue($cookie->isSecure(), 'Cookie should be Secure when HTTPS is on');
        
        unset($_SERVER['HTTPS']);
    }

    /**
     * Test createConsentCookie does not set Secure flag when HTTPS is off
     *
     * @return void
     */
    public function testCreateConsentCookieSecureFlagWithoutHttps(): void
    {
        unset($_SERVER['HTTPS']);
        
        $consent = new CookieConsent([
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
        ]);
        
        $cookie = $this->CookieConsents->createConsentCookie($consent);
        $this->assertFalse($cookie->isSecure(), 'Cookie should not be Secure when HTTPS is off');
    }

    // ============================================================
    // Get Latest Consent Tests
    // ============================================================

    /**
     * Test getLatestConsent with user_id returns correct record
     *
     * @return void
     */
    public function testGetLatestConsentWithUserId(): void
    {
        $userId = '2bf8ea83-4865-40c1-a982-65b8a0351449';
        $result = $this->CookieConsents->getLatestConsent(null, $userId);
        
        $this->assertNotNull($result, 'Should return a consent record for valid user_id');
        $this->assertEquals($userId, $result['user_id'], 'Returned record should match requested user_id');
        $this->assertEquals('7e5ce039-c98b-40bf-8bf5-72b330be2e5d', $result['id'], 'Should return the most recent record');
    }

    /**
     * Test getLatestConsent with session_id returns correct record
     *
     * @return void
     */
    public function testGetLatestConsentWithSessionId(): void
    {
        $sessionId = 'session_guest_456';
        $result = $this->CookieConsents->getLatestConsent($sessionId, null);
        
        $this->assertNotNull($result, 'Should return a consent record for valid session_id');
        $this->assertEquals($sessionId, $result['session_id'], 'Returned record should match requested session_id');
        $this->assertEquals('a1b2c3d4-e5f6-4789-a012-3456789abcde', $result['id'], 'Should return the most recent record');
    }

    /**
     * Test getLatestConsent prioritizes user_id over session_id
     *
     * @return void
     */
    public function testGetLatestConsentPrioritizesUserId(): void
    {
        $userId = '2bf8ea83-4865-40c1-a982-65b8a0351449';
        $sessionId = 'session_user_123';
        
        $result = $this->CookieConsents->getLatestConsent($sessionId, $userId);
        
        $this->assertNotNull($result, 'Should return a consent record');
        $this->assertEquals($userId, $result['user_id'], 'Should prioritize user_id lookup');
    }

    /**
     * Test getLatestConsent returns latest by created timestamp
     *
     * @return void
     */
    public function testGetLatestConsentReturnsLatestByCreated(): void
    {
        $userId = '2bf8ea83-4865-40c1-a982-65b8a0351449';
        $result = $this->CookieConsents->getLatestConsent(null, $userId);
        
        $this->assertNotNull($result, 'Should return a consent record');
        // Record with ID '7e5ce039...' has created date '2025-10-07 15:12:55'
        // Record with ID 'c3d4e5f6...' has created date '2025-10-01 12:00:00'
        $this->assertEquals('7e5ce039-c98b-40bf-8bf5-72b330be2e5d', $result['id'], 'Should return record with latest created timestamp');
    }

    /**
     * Test getLatestConsent returns null when both parameters are null
     *
     * @return void
     */
    public function testGetLatestConsentWithBothNull(): void
    {
        $result = $this->CookieConsents->getLatestConsent(null, null);
        $this->assertNull($result, 'Should return null when both session_id and user_id are null');
    }

    /**
     * Test getLatestConsent returns null when no records match
     *
     * @return void
     */
    public function testGetLatestConsentWithNoMatches(): void
    {
        $result = $this->CookieConsents->getLatestConsent('non_existent_session', '00000000-0000-0000-0000-000000000000');
        $this->assertNull($result, 'Should return null when no records match the criteria');
    }

    /**
     * Test getLatestConsent returns only specified fields
     *
     * @return void
     */
    public function testGetLatestConsentReturnsCorrectFields(): void
    {
        $userId = '2bf8ea83-4865-40c1-a982-65b8a0351449';
        $result = $this->CookieConsents->getLatestConsent(null, $userId);
        
        $this->assertNotNull($result, 'Should return a consent record');
        
        $expectedFields = [
            'user_id',
            'session_id',
            'analytics_consent',
            'functional_consent',
            'marketing_consent',
            'essential_consent',
            'ip_address',
            'user_agent',
        ];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $result, "Result should contain field '{$field}'");
        }
        
        // These fields should not be included
        $this->assertArrayNotHasKey('created', $result, 'Result should not contain created field');
        $this->assertArrayNotHasKey('updated', $result, 'Result should not contain updated field');
    }
}
