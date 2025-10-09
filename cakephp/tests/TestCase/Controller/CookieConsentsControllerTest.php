<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\CookieConsentsController Test Case
 *
 * @uses \App\Controller\CookieConsentsController
 */
class CookieConsentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
    }

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
     * Test index method
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method with ID (backward compatibility)
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method without ID (new functionality)
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::edit()
     */
    public function testEditWithoutId(): void
    {
        $this->get('/en/cookie-consents/edit');
        $this->assertResponseOk();
        $this->assertResponseContains('Cookie Preferences');
        $this->assertResponseContains('Essential Cookies');
    }

    /**
     * Test POST to edit without ID creates record and sets cookie
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::edit()
     */
    public function testEditWithoutIdCreatesRecordAndSetsCookie(): void
    {
        $data = [
            'consent_type' => 'selected',
            'analytics_consent' => '1',
            'functional_consent' => '1',
            'marketing_consent' => '0',
        ];
        
        $this->post('/en/cookie-consents/edit', $data);
        $this->assertRedirect('/en/cookie-consents/edit');
        $this->assertFlashMessage('Your cookie preferences have been saved.');
        
        // Check that a cookie consent record was created
        $cookieConsents = $this->getTableLocator()->get('CookieConsents');
        $record = $cookieConsents->find()->orderBy(['created' => 'DESC'])->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->essential_consent);
        $this->assertTrue($record->analytics_consent);
        $this->assertTrue($record->functional_consent);
        $this->assertFalse($record->marketing_consent);
        
        // Check that a cookie was set in the response
        $response = $this->_response;
        $cookies = $response->getCookies();
        $this->assertArrayHasKey('consent_cookie', $cookies);
    }

    /**
     * Test POST with consent_type=all
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::edit()
     */
    public function testEditWithConsentTypeAll(): void
    {
        $data = ['consent_type' => 'all'];
        
        $this->post('/en/cookie-consents/edit', $data);
        $this->assertRedirect('/en/cookie-consents/edit');
        
        $cookieConsents = $this->getTableLocator()->get('CookieConsents');
        $record = $cookieConsents->find()->orderBy(['created' => 'DESC'])->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->essential_consent);
        $this->assertTrue($record->analytics_consent);
        $this->assertTrue($record->functional_consent);
        $this->assertTrue($record->marketing_consent);
    }

    /**
     * Test POST with consent_type=essential
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::edit()
     */
    public function testEditWithConsentTypeEssential(): void
    {
        $data = ['consent_type' => 'essential'];
        
        $this->post('/en/cookie-consents/edit', $data);
        $this->assertRedirect('/en/cookie-consents/edit');
        
        $cookieConsents = $this->getTableLocator()->get('CookieConsents');
        $record = $cookieConsents->find()->orderBy(['created' => 'DESC'])->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->essential_consent);
        $this->assertFalse($record->analytics_consent);
        $this->assertFalse($record->functional_consent);
        $this->assertFalse($record->marketing_consent);
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\CookieConsentsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
