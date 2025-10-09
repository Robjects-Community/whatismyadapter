<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SystemLogsFixture
 */
class SystemLogsFixture extends TestFixture
{
    public string $table = 'system_logs';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'level' => ['type' => 'string', 'length' => 50, 'null' => false],
        'message' => ['type' => 'text', 'null' => false],
        'context' => ['type' => 'text', 'null' => true],
        'group_name' => ['type' => 'string', 'length' => 255, 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'level_idx' => ['type' => 'index', 'columns' => ['level']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
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
            [
                'id' => 'ed3a00d4-5f21-498e-831b-712ad5c4ebbb',
                'level' => 'Lorem ipsum dolor sit amet',
                'message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'context' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-10-07 15:13:38',
                'group_name' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
