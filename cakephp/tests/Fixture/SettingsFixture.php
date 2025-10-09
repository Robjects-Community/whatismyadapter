<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
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
                'id' => 'bfb555d0-42fc-47d5-b939-02bbc2da662b',
                'ordering' => 3,
                'category' => 'Security',
                'key_name' => 'blockOnNoIp',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => 0,
                'description' => 'Block requests when the client IP address cannot be determined.',
                'data' => null,
                'column_width' => 2,
                'created' => '2025-07-12 21:49:06',
                'modified' => '2025-07-12 21:49:06',
            ],
            [
                'id' => '675643e4-c4f2-4675-86b9-5a978ff583a9',
                'ordering' => 4,
                'category' => 'Security',
                'key_name' => 'enableRateLimiting',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => 0,
                'description' => 'Enable rate limiting for IP addresses.',
                'data' => null,
                'column_width' => 2,
                'created' => '2025-07-12 21:49:06',
                'modified' => '2025-07-12 21:49:06',
            ],
            [
                'id' => '3de8a6bb-1234-4cde-8abc-abcdefabcdef',
                'ordering' => 1,
                'category' => 'ImageSizes',
                'key_name' => 'massive',
                'value' => '800',
                'value_type' => 'numeric',
                'value_obscure' => 0,
                'description' => 'Massive image size width',
                'data' => null,
                'column_width' => 2,
                'created' => '2024-09-20 10:00:00',
                'modified' => '2024-09-27 12:00:00',
            ],
            [
                'id' => '4ee9b7cc-2345-4def-9abc-bcdefabcdef0',
                'ordering' => 1,
                'category' => 'Email',
                'key_name' => 'reply_email',
                'value' => 'noreply@example.com',
                'value_type' => 'text',
                'value_obscure' => 0,
                'description' => 'Reply-to email address',
                'data' => null,
                'column_width' => 2,
                'created' => '2024-09-20 10:00:00',
                'modified' => '2024-09-27 12:00:00',
            ],
        ];
        parent::init();
    }
}
