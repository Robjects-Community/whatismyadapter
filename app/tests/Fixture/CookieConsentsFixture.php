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
     * Table name
     *
     * @var string
     */
    public string $table = 'cookie_consents';

    /**
     * Fields
     *
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'string', 'length' => 36, 'null' => false],
        'user_id' => ['type' => 'string', 'length' => 36, 'null' => true],
        'session_id' => ['type' => 'string', 'length' => 255, 'null' => true],
        'analytics_consent' => ['type' => 'boolean', 'null' => false, 'default' => 0],
        'functional_consent' => ['type' => 'boolean', 'null' => false, 'default' => 0],
        'marketing_consent' => ['type' => 'boolean', 'null' => false, 'default' => 0],
        'essential_consent' => ['type' => 'boolean', 'null' => false, 'default' => 1],
        'ip_address' => ['type' => 'string', 'length' => 45, 'null' => false],
        'user_agent' => ['type' => 'string', 'length' => 255, 'null' => false],
        'created' => ['type' => 'datetime', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            // Record 1: All consents true, has user_id and session_id
            [
                'id' => '7e5ce039-c98b-40bf-8bf5-72b330be2e5d',
                'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
                'session_id' => 'session_user_123',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created' => '2025-10-07 15:12:55',
            ],
            // Record 2: All consents false, only session_id (no user_id)
            [
                'id' => 'a1b2c3d4-e5f6-4789-a012-3456789abcde',
                'user_id' => null,
                'session_id' => 'session_guest_456',
                'analytics_consent' => 0,
                'functional_consent' => 0,
                'marketing_consent' => 0,
                'essential_consent' => 1,
                'ip_address' => '10.0.0.25',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created' => '2025-10-06 10:30:00',
            ],
            // Record 3: Mixed consents, only user_id (no session_id)
            [
                'id' => 'b2c3d4e5-f6a7-4890-b123-4567890bcdef',
                'user_id' => '3cf9fb94-5976-51d2-b093-76c9b1461550',
                'session_id' => null,
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 0,
                'essential_consent' => 1,
                'ip_address' => '172.16.0.100',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15',
                'created' => '2025-10-05 08:15:30',
            ],
            // Record 4: Another record for same user, older timestamp (for testing ordering)
            [
                'id' => 'c3d4e5f6-a7b8-4901-c234-567890cdefab',
                'user_id' => '2bf8ea83-4865-40c1-a982-65b8a0351449',
                'session_id' => 'session_user_123_old',
                'analytics_consent' => 0,
                'functional_consent' => 1,
                'marketing_consent' => 0,
                'essential_consent' => 1,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created' => '2025-10-01 12:00:00',
            ],
            // Record 5: Another record for same session, older timestamp (for testing ordering)
            [
                'id' => 'd4e5f6a7-b8c9-4012-d345-67890defabcd',
                'user_id' => null,
                'session_id' => 'session_guest_456',
                'analytics_consent' => 1,
                'functional_consent' => 1,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '10.0.0.25',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created' => '2025-09-30 18:45:00',
            ],
            // Record 6: IPv6 address and different user agent
            [
                'id' => 'e5f6a7b8-c9d0-4123-e456-7890efabcdef',
                'user_id' => '4dfa0c05-6087-62e3-c1a4-87dac2572661',
                'session_id' => 'session_user_789',
                'analytics_consent' => 1,
                'functional_consent' => 0,
                'marketing_consent' => 1,
                'essential_consent' => 1,
                'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
                'created' => '2025-10-08 09:00:00',
            ],
        ];
        parent::init();
    }
}
