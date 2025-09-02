<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;
use Authentication\Identity;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\BlockedIpsController Test Case
 *
 * @uses \App\Controller\Admin\BlockedIpsController
 */
class BlockedIpsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.BlockedIps',
        'app.Users',
        'app.Settings',
    ];

    /**
     * @var \App\Model\Table\BlockedIpsTable
     */
    protected $BlockedIps;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->disableErrorHandlerMiddleware();
        $this->BlockedIps = TableRegistry::getTableLocator()->get('BlockedIps');

        // Configure authentication
        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);

        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->BlockedIps);
        parent::tearDown();
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Admin\BlockedIpsController::index()
     */
    public function testIndex(): void
    {
        $this->get('/admin/blocked-ips');
        $this->assertResponseOk();
        $this->assertResponseContains('192.168.1.1');
        $this->assertResponseContains('10.0.0.5');
        $this->assertResponseContains('172.16.0.1');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Admin\BlockedIpsController::view()
     */
    public function testView(): void
    {
        $this->get('/admin/blocked-ips/view/550e8400-e29b-41d4-a716-446655440000');
        $this->assertResponseOk();
        $this->assertResponseContains('192.168.1.1');
        $this->assertResponseContains('Suspicious activity detected');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\Admin\BlockedIpsController::add()
     */
    public function testAdd(): void
    {
        $this->enableCsrfToken();
        $this->post('/admin/blocked-ips/add', [
            'ip_address' => '203.0.113.1',
            'reason' => 'Test blocking',
            'blocked_at' => DateTime::now(),
            'expires_at' => DateTime::now()->modify('+1 hour'),
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The blocked ip has been saved.');

        $blockedIp = $this->BlockedIps->find()->where(['ip_address' => '203.0.113.1'])->first();
        $this->assertNotEmpty($blockedIp);
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\Admin\BlockedIpsController::edit()
     */
    public function testEdit(): void
    {
        $this->enableCsrfToken();
        $this->put('/admin/blocked-ips/edit/550e8400-e29b-41d4-a716-446655440000', [
            'ip_address' => '192.168.1.1',
            'reason' => 'Updated reason',
        ]);

        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The blocked ip has been saved.');

        $updatedBlockedIp = $this->BlockedIps->get('550e8400-e29b-41d4-a716-446655440000');
        $this->assertEquals('Updated reason', $updatedBlockedIp->reason);
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\Admin\BlockedIpsController::delete()
     */
    public function testDelete(): void
    {
        $this->enableCsrfToken();
        $this->delete('/admin/blocked-ips/delete/550e8400-e29b-41d4-a716-446655440000');

        $this->assertResponseSuccess();
        $this->assertRedirect();
        $this->assertFlashMessage('The blocked ip has been deleted.');

        $deletedBlockedIp = $this->BlockedIps->findById('550e8400-e29b-41d4-a716-446655440000')->first();
        $this->assertEmpty($deletedBlockedIp);
    }

    /**
     * Test unauthorized access
     *
     * @return void
     */
    public function testUnauthorizedAccess(): void
    {
        // Setup non-admin user
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $identity = new Identity([
            'id' => $userId,
            'email' => 'user@example.com',
            'is_admin' => 0,
        ]);

        $this->session(['Auth' => $identity]);

        $this->get('/admin/blocked-ips');
        $this->assertRedirect('/en');
    }
}
