<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;
use Cake\TestSuite\IntegrationTestTrait;

class SystemLogsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.SystemLogs',
        'app.Users',
        'app.Settings',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);
    }

    public function testIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->get('/admin/system-logs');
        $this->assertResponseOk();
        $this->assertResponseContains('System Logs');
    }

    public function testView(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        // Use a valid UUID from your fixture
        $this->get('/admin/system-logs/view/550e8400-e29b-41d4-a716-446655440000');
        $this->assertResponseOk();
        // Check for content that actually exists in the view
        $this->assertResponseContains('Database connection failed');
    }

    public function testDelete(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->enableCsrfToken();
        // Use a valid UUID from your fixture
        $this->post('/admin/system-logs/delete/550e8400-e29b-41d4-a716-446655440000');

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The log has been deleted.');
    }

    public function testSearch(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        // Search for content that exists in the fixture data
        $this->get('/admin/system-logs?search=Database connection failed');
        $this->assertResponseOk();
        $this->assertResponseContains('Database connection failed');
    }
}
