<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CacheFixture
 */
class CacheFixture extends TestFixture
{
    public string $table = 'cache';

    public array $fields = [
        'key' => ['type' => 'string', 'length' => 255, 'null' => false],
        'value' => ['type' => 'text', 'null' => true],
        'expires' => ['type' => 'integer', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['key']],
        ],
        '_indexes' => [
            'expires_idx' => ['type' => 'index', 'columns' => ['expires']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'key' => 'test_cache_key_1',
                'value' => serialize(['data' => 'Test cache data']),
                'expires' => time() + 3600,
            ],
            [
                'key' => 'test_cache_key_2',
                'value' => serialize(['data' => 'Another cache entry']),
                'expires' => time() + 7200,
            ],
        ];
        parent::init();
    }
}
