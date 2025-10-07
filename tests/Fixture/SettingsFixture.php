<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SettingsFixture extends TestFixture
{
    public string $table = 'settings';

    public function init(): void
    {
        $this->records = [
            [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'category' => 'i18n',
                'key_name' => 'locale',
                'value_type' => 'text',
                'value' => 'en_GB',
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'category' => 'AI',
                'key_name' => 'hourlyLimit',
                'value_type' => 'numeric',
                'value' => '100',
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
            [
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'category' => 'AI',
                'key_name' => 'dailyCostLimit',
                'value_type' => 'numeric',
                'value' => '5',
                'created' => '2025-08-10 00:00:00',
                'modified' => '2025-08-10 00:00:00',
            ],
        ];
        parent::init();
    }
}
