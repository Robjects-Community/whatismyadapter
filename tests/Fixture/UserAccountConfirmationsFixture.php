<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UserAccountConfirmationsFixture
 */
class UserAccountConfirmationsFixture extends TestFixture
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
                'id' => 'b39e7681-ac60-4536-86a0-d0845789f772',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // admin user ID
                'confirmation_code' => 'CONFIRM123ADMIN', // unique confirmation code for admin
                'created' => '2024-10-06 11:49:16',
            ],
            [
                'id' => 'b39e7681-ac60-4536-86a0-d0845789f773',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02', // regular user ID
                'confirmation_code' => 'CONFIRM123USER', // unique confirmation code for user
                'created' => '2024-10-06 11:50:16',
            ],
            [
                'id' => 'c50e1234-ac60-6354-86a0-d0845798f882',
                'user_id' => '6509480c-e7e6-4e65-9c38-8574a8d09d02', // regular user ID
                'confirmation_code' => 'CONFIRM123USER1', // unique confirmation code for user
                'created' => '2024-11-06 11:50:16',
            ],
        ];
        parent::init();
    }
}
