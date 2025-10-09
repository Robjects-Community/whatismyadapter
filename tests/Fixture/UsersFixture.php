<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public string $table = 'users';

    public function init(): void
    {
        $this->records = [
            [
                'id' => '00000000-0000-0000-0000-000000000001',
                'username' => 'admin',
                'password' => '$2y$10$abcdefghijklmnopqrstuv',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'is_admin' => 1,
                'active' => 1,
                'image' => null,
                'keywords' => null,
                'alt_text' => null,
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'username' => 'user',
                'password' => '$2y$10$abcdefghijklmnopqrstuv',
                'email' => 'user@example.com',
                'role' => 'user',
                'is_admin' => 0,
                'active' => 1,
                'image' => null,
                'keywords' => null,
                'alt_text' => null,
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
        ];
        parent::init();
    }
}
