<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\CookieConsent;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\CookieConsent Test Case
 *
 * Tests for CookieConsent entity including:
 * - Consent checking helper methods
 * - Field accessibility configuration
 * - Entity data handling
 */
class CookieConsentTest extends TestCase
{
    /**
     * Test hasAnalyticsConsent returns true when analytics_consent is true
     *
     * @return void
     */
    public function testHasAnalyticsConsentReturnsTrue(): void
    {
        $consent = new CookieConsent(['analytics_consent' => true]);
        $this->assertTrue($consent->hasAnalyticsConsent(), 'Expected analytics consent to be true');
    }

    /**
     * Test hasAnalyticsConsent returns false when analytics_consent is false
     *
     * @return void
     */
    public function testHasAnalyticsConsentReturnsFalse(): void
    {
        $consent = new CookieConsent(['analytics_consent' => false]);
        $this->assertFalse($consent->hasAnalyticsConsent(), 'Expected analytics consent to be false');
    }

    /**
     * Test hasAnalyticsConsent handles integer values
     *
     * @return void
     */
    public function testHasAnalyticsConsentWithIntegerValues(): void
    {
        $consentTrue = new CookieConsent(['analytics_consent' => 1]);
        $this->assertTrue($consentTrue->hasAnalyticsConsent(), 'Expected analytics consent with value 1 to be true');

        $consentFalse = new CookieConsent(['analytics_consent' => 0]);
        $this->assertFalse($consentFalse->hasAnalyticsConsent(), 'Expected analytics consent with value 0 to be false');
    }

    /**
     * Test hasFunctionalConsent returns true when functional_consent is true
     *
     * @return void
     */
    public function testHasFunctionalConsentReturnsTrue(): void
    {
        $consent = new CookieConsent(['functional_consent' => true]);
        $this->assertTrue($consent->hasFunctionalConsent(), 'Expected functional consent to be true');
    }

    /**
     * Test hasFunctionalConsent returns false when functional_consent is false
     *
     * @return void
     */
    public function testHasFunctionalConsentReturnsFalse(): void
    {
        $consent = new CookieConsent(['functional_consent' => false]);
        $this->assertFalse($consent->hasFunctionalConsent(), 'Expected functional consent to be false');
    }

    /**
     * Test hasFunctionalConsent handles integer values
     *
     * @return void
     */
    public function testHasFunctionalConsentWithIntegerValues(): void
    {
        $consentTrue = new CookieConsent(['functional_consent' => 1]);
        $this->assertTrue($consentTrue->hasFunctionalConsent(), 'Expected functional consent with value 1 to be true');

        $consentFalse = new CookieConsent(['functional_consent' => 0]);
        $this->assertFalse($consentFalse->hasFunctionalConsent(), 'Expected functional consent with value 0 to be false');
    }

    /**
     * Test hasMarketingConsent returns true when marketing_consent is true
     *
     * @return void
     */
    public function testHasMarketingConsentReturnsTrue(): void
    {
        $consent = new CookieConsent(['marketing_consent' => true]);
        $this->assertTrue($consent->hasMarketingConsent(), 'Expected marketing consent to be true');
    }

    /**
     * Test hasMarketingConsent returns false when marketing_consent is false
     *
     * @return void
     */
    public function testHasMarketingConsentReturnsFalse(): void
    {
        $consent = new CookieConsent(['marketing_consent' => false]);
        $this->assertFalse($consent->hasMarketingConsent(), 'Expected marketing consent to be false');
    }

    /**
     * Test hasMarketingConsent handles integer values
     *
     * @return void
     */
    public function testHasMarketingConsentWithIntegerValues(): void
    {
        $consentTrue = new CookieConsent(['marketing_consent' => 1]);
        $this->assertTrue($consentTrue->hasMarketingConsent(), 'Expected marketing consent with value 1 to be true');

        $consentFalse = new CookieConsent(['marketing_consent' => 0]);
        $this->assertFalse($consentFalse->hasMarketingConsent(), 'Expected marketing consent with value 0 to be false');
    }

    /**
     * Test that all expected fields are accessible for mass assignment
     *
     * @return void
     */
    public function testAccessibleFields(): void
    {
        $consent = new CookieConsent();
        
        $accessibleFields = [
            'user_id',
            'session_id',
            'analytics_consent',
            'functional_consent',
            'marketing_consent',
            'essential_consent',
            'ip_address',
            'user_agent',
            'created',
            'updated',
        ];

        foreach ($accessibleFields as $field) {
            $this->assertTrue(
                $consent->isAccessible($field),
                "Field '{$field}' should be accessible for mass assignment"
            );
        }
    }

    /**
     * Test that id field is not accessible for mass assignment
     *
     * @return void
     */
    public function testIdFieldNotAccessible(): void
    {
        $consent = new CookieConsent();
        $this->assertFalse(
            $consent->isAccessible('id'),
            'ID field should not be accessible for mass assignment'
        );
    }

    /**
     * Test entity can be created with all consent fields
     *
     * @return void
     */
    public function testCreateEntityWithAllConsentFields(): void
    {
        $data = [
            'user_id' => '123e4567-e89b-12d3-a456-426614174000',
            'session_id' => 'test_session_id',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ];

        $consent = new CookieConsent($data);

        $this->assertEquals($data['user_id'], $consent->user_id);
        $this->assertEquals($data['session_id'], $consent->session_id);
        $this->assertTrue($consent->analytics_consent);
        $this->assertTrue($consent->functional_consent);
        $this->assertFalse($consent->marketing_consent);
        $this->assertTrue($consent->essential_consent);
        $this->assertEquals($data['ip_address'], $consent->ip_address);
        $this->assertEquals($data['user_agent'], $consent->user_agent);
    }
}
