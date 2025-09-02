<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\CookieConsentsTable;
use App\Test\TestCase\AppControllerTestCase;
use Cake\Datasource\FactoryLocator;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\CookieConsentsController Test Case
 *
 * This test case verifies the functionality of cookie consent management,
 * including handling both authenticated and unauthenticated users, AJAX requests,
 * and GDPR compliance requirements.
 *
 * @uses \App\Controller\CookieConsentsController
 */
class CookieConsentsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Users',
        'app.CookieConsents',
        'app.Settings',
    ];

    /**
     * CookieConsentsTable instance
     *
     * @var \App\Model\Table\CookieConsentsTable
     */
    protected CookieConsentsTable $CookieConsents;

    /**
     * Setup method
     *
     * This method is called before each test. It initializes the CookieConsentsTable
     * and configures the request environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->disableErrorHandlerMiddleware();
        $this->CookieConsents = FactoryLocator::get('Table')->get('CookieConsents');
    }

    /**
     * Test edit access for unauthenticated users
     *
     * Verifies that unauthenticated users can access the cookie consent form.
     *
     * @return void
     */
    public function testEditAccessUnauthenticated(): void
    {
        $this->get('/en/cookie-consents/edit');
        $this->assertResponseOk();
        $this->assertResponseContains('Cookie Preferences');
    }

    /**
     * Test edit access for authenticated users
     *
     * Ensures authenticated users can access their cookie preferences
     * and see their existing consent settings.
     *
     * @return void
     */
    public function testEditAccessAuthenticated(): void
    {
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->loginUser($userId);

        $this->get('/en/cookie-consents/edit');
        $this->assertResponseOk();
        $this->assertResponseContains('Cookie Preferences');
    }

    /**
     * Test successful cookie consent submission for unauthenticated user
     *
     * Verifies that an unauthenticated user can successfully save their
     * cookie preferences with proper GDPR compliance data.
     *
     * @return void
     */
    public function testEditSubmitUnauthenticated(): void
    {
        $this->enableCsrfToken();

        $data = [
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
        ];

        $this->post('/en/cookie-consents/edit', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('Your cookie preferences have been saved.');

        // Verify GDPR compliance data was saved
        $latest = $this->CookieConsents->find()
            ->orderBy(['created' => 'DESC'])
            ->first();

        $this->assertTrue($latest->analytics_consent);
        $this->assertTrue($latest->functional_consent);
        $this->assertFalse($latest->marketing_consent);
        $this->assertTrue($latest->essential_consent);
        $this->assertEquals('cli', $latest->session_id);
    }

    /**
     * Test successful cookie consent submission for authenticated user
     *
     * Verifies that an authenticated user can successfully save their
     * cookie preferences and that their user ID is properly recorded.
     *
     * @return void
     */
    public function testEditSubmitAuthenticated(): void
    {
        $this->enableCsrfToken();
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->loginUser($userId);

        $data = [
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
        ];

        $this->post('/en/cookie-consents/edit', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('Your cookie preferences have been saved.');

        // Verify user ID was saved
        $latest = $this->CookieConsents->find()
            ->orderBy(['created' => 'DESC'])
            ->first();

        $this->assertEquals($userId, $latest->user_id);
    }

    /**
     * Test AJAX request to view cookie consent form
     *
     * Ensures that AJAX requests return the appropriate layout and content.
     *
     * @return void
     */
    public function testEditAjaxGet(): void
    {
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        $this->get('/en/cookie-consents/edit');
        $this->assertResponseOk();
        $this->assertResponseContains('Please select your cookie preferences below. Essential cookies are required for the website to function and cannot be disabled.');
        $this->assertResponseNotContains('<!DOCTYPE html>'); // Should use ajax layout
    }

    /**
     * Test AJAX submission of cookie consent
     *
     * Verifies that AJAX submissions return appropriate JSON responses.
     *
     * @return void
     */
    public function testEditAjaxSubmit(): void
    {
        $this->enableCsrfToken();
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        $data = [
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
        ];

        $this->post('/en/cookie-consents/edit', $data);
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $this->assertResponseContains('"success":true');
    }

    /**
     * Test consent history tracking
     *
     * Verifies that multiple consent submissions create new records
     * rather than updating existing ones, maintaining an audit trail.
     *
     * @return void
     */
    public function testConsentHistoryTracking(): void
    {
        $this->enableCsrfToken();
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->loginUser($userId);

        // Initial consent
        $this->post('/en/cookie-consents/edit', [
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ]);

        // Updated consent
        $this->post('/en/cookie-consents/edit', [
            'analytics_consent' => false,
            'functional_consent' => true,
            'marketing_consent' => false,
            'essential_consent' => true,
        ]);

        // Verify we have multiple records for the same user
        $consentHistory = $this->CookieConsents->find()
            ->where(['user_id' => $userId])
            ->orderBy(['created' => 'DESC'])
            ->toArray();

        $this->assertGreaterThan(1, count($consentHistory));
        $this->assertNotEquals(
            $consentHistory[0]->analytics_consent,
            $consentHistory[1]->analytics_consent,
        );
    }
}
