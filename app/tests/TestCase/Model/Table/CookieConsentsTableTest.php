<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CookieConsentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CookieConsentsTable Test Case
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

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CookieConsentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CookieConsentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test createConsentCookie method
     *
     * @return void
     * @link \App\Model\Table\CookieConsentsTable::createConsentCookie()
     */
    public function testCreateConsentCookie(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getLatestConsent method
     *
     * @return void
     * @link \App\Model\Table\CookieConsentsTable::getLatestConsent()
     */
    public function testGetLatestConsent(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
