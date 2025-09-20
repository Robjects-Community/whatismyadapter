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
                'id' => '57f8a9fe-9bcc-48e5-9884-70498adc761a',
                'user_id' => '6fd2dea0-354a-44bc-9340-4010db901128',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'user_agent' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-07-12 21:48:39',
            ],
        ];
        parent::init();
    }
}
