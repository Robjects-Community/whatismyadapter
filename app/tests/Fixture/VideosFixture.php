<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VideosFixture
 */
class VideosFixture extends TestFixture
{
    public string $table = 'videos';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false],
        'description' => ['type' => 'text', 'null' => true],
        'url' => ['type' => 'string', 'length' => 500, 'null' => false],
        'thumbnail' => ['type' => 'string', 'length' => 500, 'null' => true],
        'duration' => ['type' => 'integer', 'null' => true],
        'is_published' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique_slug' => ['type' => 'unique', 'columns' => ['slug']],
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
                'id' => 'a1b2c3d4-e5f6-7890-1234-567890abcdef',
                'title' => 'Sample Video',
                'slug' => 'sample-video',
                'description' => 'This is a sample video for testing purposes.',
                'url' => 'https://example.com/video.mp4',
                'thumbnail' => 'https://example.com/thumbnail.jpg',
                'duration' => 120,
                'is_published' => true,
                'created' => '2025-10-07 15:00:00',
                'modified' => '2025-10-07 15:00:00',
            ],
        ];
        parent::init();
    }
}
