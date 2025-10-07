<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Model\Entity\User;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class AiMetricsControllerSqliteTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.AiMetrics'];


    protected function loginAsAdmin(): void
    {
        // Create a minimal User entity that can pass admin checks
        $user = new User([
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'username' => 'admin',
            'is_admin' => true,
            'active' => 1,
        ]);

        // Authentication plugin will read from session key 'Auth'
        $this->session(['Auth' => $user]);
    }

    public function testRealtimeDataRequiresAuthRedirects(): void
    {
        $this->get('/admin/ai-metrics/realtime-data');
        $this->assertRedirect();
    }

    public function testRealtimeDataSuccessDefaultTimeframe(): void
    {
        $this->loginAsAdmin();

        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/admin/ai-metrics/realtime-data');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('success', $payload);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('timeframe', $payload);
    }

    public function testRealtimeDataWithSpecificTimeframes(): void
    {
        $this->loginAsAdmin();

        foreach (['1h', '24h', '7d', '30d'] as $tf) {
            $this->configRequest(['headers' => ['Accept' => 'application/json']]);
            $this->get('/admin/ai-metrics/realtime-data?timeframe=' . $tf);
            $this->assertResponseOk();
            $payload = json_decode((string)$this->_response->getBody(), true);
            $this->assertSame($tf, $payload['timeframe'] ?? null);
        }
    }
}