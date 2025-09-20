<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class AddSecuritySettings extends AbstractMigration
{
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                [
                    'id' => Text::uuid(),
                    'ordering' => 2,
                    'category' => 'Security',
                    'key_name' => 'trustedProxies',
                    'value' => '',
                    'value_type' => 'textarea',
                    'value_obscure' => false,
                    'description' => 'List of trusted proxy IP addresses (one per line). Only requests from these IPs will have their forwarded headers honored when trustProxy is enabled. Leave empty to trust all proxies (not recommended for production).',
                    'data' => null,
                    'column_width' => 6,
                ],
                [
                    'id' => Text::uuid(),
                    'ordering' => 3,
                    'category' => 'Security',
                    'key_name' => 'blockOnNoIp',
                    'value' => '1',
                    'value_type' => 'bool',
                    'value_obscure' => false,
                    'description' => 'Block requests when the client IP address cannot be determined. Recommended for production environments to prevent IP detection bypass.',
                    'data' => null,
                    'column_width' => 2,
                ],
                [
                    'id' => Text::uuid(),
                    'ordering' => 4,
                    'category' => 'Security',
                    'key_name' => 'enableRateLimiting',
                    'value' => '1',
                    'value_type' => 'bool',
                    'value_obscure' => false,
                    'description' => 'Enable rate limiting for IP addresses. When enabled, the system will track request frequency and temporarily block IPs that exceed the configured limits.',
                    'data' => null,
                    'column_width' => 2,
                ],
            ])
            ->save();
    }
}