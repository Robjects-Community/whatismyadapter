<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
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
            [
                'id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'is_admin' => 1,
                'role' => 'Lorem ipsum dolor sit amet',
                'role_id' => '92eda966-6dcf-4563-a2f8-4d9d77c6a2e3',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'image' => '',
                'alt_text' => 'Lorem ipsum dolor sit amet',
                'keywords' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'dir' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
                'mime' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:13:47',
                'modified' => '2025-10-07 15:13:47',
                'username' => 'Lorem ipsum dolor sit amet',
                'active' => 1,
                'reset_token' => 'Lorem ipsum dolor sit amet',
                'reset_token_expires' => '2025-10-07 15:13:47',
            ],
        ];
        parent::init();
    }
}
