<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QueueConfigurationsFixture
 */
class QueueConfigurationsFixture extends TestFixture
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
                'id' => '593f36d3-0343-4b7e-8447-a4bb9343173d',
                'name' => 'Lorem ipsum dolor sit amet',
                'config_key' => 'Lorem ipsum dolor sit amet',
                'queue_type' => 'Lorem ipsum dolor ',
                'queue_name' => 'Lorem ipsum dolor sit amet',
                'host' => 'Lorem ipsum dolor sit amet',
                'port' => 1,
                'username' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'database' => 1,
                'vhost' => 'Lorem ipsum dolor sit amet',
                'exchange' => 'Lorem ipsum dolor sit amet',
                'routing_key' => 'Lorem ipsum dolor sit amet',
                'ssl_enabled' => 1,
                'persistent' => 1,
                'max_workers' => 1,
                'priority' => 1,
                'enabled' => 1,
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'config_data' => '',
                'created' => '2025-09-17 23:54:44',
                'modified' => '2025-09-17 23:54:44',
            ],
        ];
        parent::init();
    }
}
