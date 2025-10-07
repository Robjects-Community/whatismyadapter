<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 * Table schema is loaded from app/tests/schema/users.php during test bootstrap
 */
class UsersFixture extends TestFixture
{

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            // Admin user for testing admin functionality
            [
                'id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'is_admin' => 1,
                'role' => 'admin',
                'role_id' => '92eda966-6dcf-4563-a2f8-4d9d77c6a2e3',
                'email' => 'admin@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuvwxyz1234567', // hashed 'password'
                'image' => '',
                'alt_text' => 'Admin user avatar',
                'keywords' => 'admin, test',
                'name' => 'Admin User',
                'dir' => '/uploads/users',
                'size' => 1024,
                'mime' => 'image/png',
                'created' => '2025-10-07 15:13:47',
                'modified' => '2025-10-07 15:13:47',
                'username' => 'admin',
                'active' => 1,
                'reset_token' => null,
                'reset_token_expires' => null,
            ],
            // Regular user for testing standard functionality
            [
                'id' => '91d91e66-5d90-412b-aeaa-4d51fa110795',
                'is_admin' => 0,
                'role' => 'user',
                'role_id' => '93eda966-6dcf-4563-a2f8-4d9d77c6a2e4',
                'email' => 'user@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuvwxyz1234567', // hashed 'password'
                'image' => '',
                'alt_text' => 'Regular user avatar',
                'keywords' => 'user, test',
                'name' => 'Regular User',
                'dir' => '/uploads/users',
                'size' => 1024,
                'mime' => 'image/png',
                'created' => '2025-10-07 15:13:47',
                'modified' => '2025-10-07 15:13:47',
                'username' => 'user',
                'active' => 1,
                'reset_token' => null,
                'reset_token_expires' => null,
            ],
            // Inactive user for testing inactive account scenarios
            [
                'id' => '92d91e66-5d90-412b-aeaa-4d51fa110796',
                'is_admin' => 0,
                'role' => 'user',
                'role_id' => '93eda966-6dcf-4563-a2f8-4d9d77c6a2e4',
                'email' => 'inactive@example.com',
                'password' => '$2y$10$abcdefghijklmnopqrstuvwxyz1234567', // hashed 'password'
                'image' => '',
                'alt_text' => 'Inactive user avatar',
                'keywords' => 'inactive, test',
                'name' => 'Inactive User',
                'dir' => '/uploads/users',
                'size' => 1024,
                'mime' => 'image/png',
                'created' => '2025-10-07 15:13:47',
                'modified' => '2025-10-07 15:13:47',
                'username' => 'inactive',
                'active' => 0,
                'reset_token' => null,
                'reset_token_expires' => null,
            ],
        ];
        parent::init();
    }
}
