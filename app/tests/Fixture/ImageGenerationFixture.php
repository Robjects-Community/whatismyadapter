<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImageGenerationFixture
 */
class ImageGenerationFixture extends TestFixture
{
    public string $table = 'image_generations';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'user_id' => ['type' => 'uuid', 'null' => true],
        'prompt' => ['type' => 'text', 'null' => false],
        'model' => ['type' => 'string', 'length' => 100, 'null' => false],
        'image_url' => ['type' => 'string', 'length' => 500, 'null' => true],
        'image_path' => ['type' => 'string', 'length' => 255, 'null' => true],
        'width' => ['type' => 'integer', 'null' => true],
        'height' => ['type' => 'integer', 'null' => true],
        'status' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => 'pending'],
        'error_message' => ['type' => 'text', 'null' => true],
        'cost' => ['type' => 'decimal', 'length' => 10, 'precision' => 4, 'null' => true],
        'generation_time_ms' => ['type' => 'integer', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'user_id_idx' => ['type' => 'index', 'columns' => ['user_id']],
            'status_idx' => ['type' => 'index', 'columns' => ['status']],
            'created_idx' => ['type' => 'index', 'columns' => ['created']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-img-1111-1111-111111111111',
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'prompt' => 'A professional product photo of a USB cable',
                'model' => 'dall-e-3',
                'image_url' => 'https://example.com/generated/image1.png',
                'image_path' => '/uploads/generated/image1.png',
                'width' => 1024,
                'height' => 1024,
                'status' => 'completed',
                'error_message' => null,
                'cost' => 0.04,
                'generation_time_ms' => 5000,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '22222222-img-2222-2222-222222222222',
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'prompt' => 'Technical diagram of HDMI connector',
                'model' => 'dall-e-3',
                'image_url' => null,
                'image_path' => null,
                'width' => null,
                'height' => null,
                'status' => 'failed',
                'error_message' => 'Generation limit exceeded',
                'cost' => null,
                'generation_time_ms' => null,
                'created' => '2025-01-01 01:00:00',
                'modified' => '2025-01-01 01:00:00',
            ],
        ];
        parent::init();
    }
}
