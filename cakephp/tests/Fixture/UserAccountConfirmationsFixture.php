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
                'id' => '4de3eb82-27ba-43a9-bdb6-96e020b62fbe',
                'user_id' => 'Lorem ipsum dolor sit amet',
                'confirmation_code' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-07-12 21:49:09',
            ],
        ];
        parent::init();
    }
}
