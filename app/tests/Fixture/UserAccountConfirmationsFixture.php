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
                'id' => '9c9e414a-7a09-4296-b71e-1ad8199cb2b1',
                'user_id' => 'Lorem ipsum dolor sit amet',
                'confirmation_code' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:13:45',
            ],
        ];
        parent::init();
    }
}
