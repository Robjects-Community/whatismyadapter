<?php
declare(strict_types=1);

namespace App\TestSuite;

use Authentication\Identity;

trait AdminAuthTrait
{
    protected function loginAsAdmin(array $overrides = []): void
    {
        $user = array_merge([
            'id' => '00000000-0000-0000-0000-000000000001',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'is_admin' => 1,
            'role' => 'admin',
        ], $overrides);

        // Create identity and set it in the session
        $identity = new Identity($user);
        $this->session(['Auth' => $identity]);

        // Also set it as request attribute for the Authentication component
        $this->configRequest([
            'attributes' => ['identity' => $identity],
        ]);
    }
}
