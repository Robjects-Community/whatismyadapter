<?php
declare(strict_types=1);

namespace App\Test\Support;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * Reusable helpers for controller integration tests.
 */
trait IntegrationTestHelperTrait
{
    use IntegrationTestTrait;

    /**
     * Log in as an admin user by setting the session identity directly.
     */
    protected function loginAsAdmin(): void
    {
        $user = new User([
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'username' => 'admin',
            'is_admin' => true,
            'active' => 1,
        ]);
        $this->session(['Auth' => $user]);
    }

    /**
     * Log in as a regular user.
     */
    protected function loginAsUser(): void
    {
        $user = new User([
            'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'username' => 'user',
            'is_admin' => false,
            'active' => 1,
        ]);
        $this->session(['Auth' => $user]);
    }

    /**
     * Send a GET request expecting JSON response and return decoded payload.
     *
     * @return array<string,mixed>|null
     */
    protected function jsonGet(string $url): ?array
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get($url);
        $this->assertResponseOk();

        return json_decode((string)$this->_response->getBody(), true);
    }

    /**
     * Send a POST request with JSON body and return decoded payload.
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>|null
     */
    protected function jsonPost(string $url, array $data): ?array
    {
        $this->configRequest(['headers' => ['Content-Type' => 'application/json']]);
        $this->post($url, json_encode($data));
        $this->assertResponseOk();

        return json_decode((string)$this->_response->getBody(), true);
    }

    /**
     * Basic assertion helper for success payloads.
     */
    protected function assertJsonSuccess(array $payload): void
    {
        $this->assertArrayHasKey('success', $payload);
        $this->assertTrue((bool)$payload['success']);
    }
}
