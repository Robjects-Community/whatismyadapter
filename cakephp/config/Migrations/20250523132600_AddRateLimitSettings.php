<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class AddRateLimitSettings extends AbstractMigration
{
    public function change(): void
    {
        $settings = [
            // Route-specific settings for login
            [
                'id' => Text::uuid(),
                'ordering' => 10,
                'category' => 'RateLimit',
                'key_name' => 'loginNumberOfRequests',
                'value' => '5',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum login attempts allowed within the time window.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 11,
                'category' => 'RateLimit',
                'key_name' => 'loginNumberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Time window in seconds for login rate limiting.',
                'data' => null,
                'column_width' => 2,
            ],
            // Admin route settings
            [
                'id' => Text::uuid(),
                'ordering' => 12,
                'category' => 'RateLimit',
                'key_name' => 'adminNumberOfRequests',
                'value' => '40',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum admin area requests allowed within the time window.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 13,
                'category' => 'RateLimit',
                'key_name' => 'adminNumberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Time window in seconds for admin area rate limiting.',
                'data' => null,
                'column_width' => 2,
            ],
            // Password reset settings
            [
                'id' => Text::uuid(),
                'ordering' => 14,
                'category' => 'RateLimit',
                'key_name' => 'passwordResetNumberOfRequests',
                'value' => '3',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum password reset requests allowed within the time window.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 15,
                'category' => 'RateLimit',
                'key_name' => 'passwordResetNumberOfSeconds',
                'value' => '300',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Time window in seconds for password reset rate limiting (300 = 5 minutes).',
                'data' => null,
                'column_width' => 2,
            ],
            // Registration settings
            [
                'id' => Text::uuid(),
                'ordering' => 16,
                'category' => 'RateLimit',
                'key_name' => 'registerNumberOfRequests',
                'value' => '5',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum registration requests allowed within the time window.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 17,
                'category' => 'RateLimit',
                'key_name' => 'registerNumberOfSeconds',
                'value' => '300',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Time window in seconds for registration rate limiting (300 = 5 minutes).',
                'data' => null,
                'column_width' => 2,
            ],
            // Suspicious activity settings
            [
                'id' => Text::uuid(),
                'ordering' => 20,
                'category' => 'Security',
                'key_name' => 'suspiciousRequestThreshold',
                'value' => '3',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Number of suspicious requests before blocking an IP.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 21,
                'category' => 'Security',
                'key_name' => 'suspiciousWindowHours',
                'value' => '24',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Time window in hours for counting suspicious requests.',
                'data' => null,
                'column_width' => 2,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 22,
                'category' => 'Security',
                'key_name' => 'suspiciousBlockHours',
                'value' => '24',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'How long to block IPs that exceed the suspicious request threshold (in hours).',
                'data' => null,
                'column_width' => 2,
            ],
        ];

        $this->table('settings')->insert($settings)->save();

        // Update existing RateLimit settings
        $this->execute("UPDATE `settings` SET `value` = '30' WHERE `settings`.`category` = 'RateLimit' and `settings`.`key_name` = 'numberOfRequests';");
    }
}