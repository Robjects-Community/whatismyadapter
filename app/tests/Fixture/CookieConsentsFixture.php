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
                'id' => 'a40ec6c5-a811-41e7-bced-8fa8fea742d3',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // admin@example.com
                'session_id' => 'session_admin',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created' => '2024-11-16 17:35:45',
                'updated' => '2024-11-16 17:35:45',
            ],
            [
                'id' => 'b50ec6c5-a811-41e7-bced-8fa8fea742d4',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02', // user@example.com
                'session_id' => 'session_user',
                'analytics_consent' => 1,
                'functional_consent' => 0,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.2',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X)',
                'created' => '2024-11-16 17:36:45',
                'updated' => '2024-11-16 17:36:45',
            ],
            [
                'id' => 'c60ec6c5-a811-41e7-bced-8fa8fea742d5',
                'user_id' => '6509480c-e7e6-4e65-9c38-8574a8d09d02', // user1@example.com
                'session_id' => 'session_user1',
                'analytics_consent' => 0,
                'functional_consent' => 1,
                'marketing_consent' => 0,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.3',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS)',
                'created' => '2024-11-16 17:37:45',
                'updated' => '2024-11-16 17:37:45',
            ],
            [
                'id' => 'd70ec6c5-a811-41e7-bced-8fa8fea742d6',
                'user_id' => '6509480c-e7e6-34hy-9c38-8574a8d09d02', // user2@example.com
                'session_id' => 'session_user2',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.4',
                'user_agent' => 'Mozilla/5.0 (Linux; Android)',
                'created' => '2024-11-16 17:38:45',
                'updated' => '2024-11-16 17:38:45',
            ],
            [
                'id' => 'e80ec6c5-a811-41e7-bced-8fa8fea742d7',
                'user_id' => 'qwde480c-e7e6-34hy-9c38-8574a8d09d02', // user3@example.com
                'session_id' => 'session_user3',
                'analytics_consent' => 0,
                'functional_consent' => 0,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.5',
                'user_agent' => 'Mozilla/5.0 (iPad; CPU OS)',
                'created' => '2024-11-16 17:39:45',
                'updated' => '2024-11-16 17:39:45',
            ],
        ];
        parent::init();
    }
}
