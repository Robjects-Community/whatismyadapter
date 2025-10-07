<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CookieConsentsFixture
 */
class CookieConsentsFixture extends TestFixture
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
                'id' => '7e5ce039-c98b-40bf-8bf5-72b330be2e5d',
                'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'user_agent' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:12:55',
            ],
        ];
        parent::init();
    }
}
