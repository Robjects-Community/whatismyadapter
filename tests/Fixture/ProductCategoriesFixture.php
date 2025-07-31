<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductCategoriesFixture
 */
class ProductCategoriesFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'product_categories';

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'cat-001-charging-cables',
                'name' => 'Charging Cables',
                'slug' => 'charging-cables',
                'description' => 'Cables primarily designed for device charging',
                'parent_id' => null,
                'lft' => 1,
                'rght' => 2,
                'icon' => null,
                'color' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'created' => '2024-01-01 10:00:00',
                'modified' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 'cat-002-video-adapters',
                'name' => 'Video Adapters',
                'slug' => 'video-adapters',
                'description' => 'Adapters for video signal conversion',
                'parent_id' => null,
                'lft' => 3,
                'rght' => 4,
                'icon' => null,
                'color' => null,
                'is_active' => 1,
                'sort_order' => 1,
                'created' => '2024-01-01 10:00:00',
                'modified' => '2024-01-01 10:00:00'
            ]
        ];
        parent::init();
    }
}
