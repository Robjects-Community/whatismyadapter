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
                'description' => 'Cables designed primarily for device charging',
                'parent_id' => null,
                'lft' => 1,
                'rght' => 4,
                'icon' => 'power-plug',
                'color' => '#28a745',
                'is_active' => 1,
                'sort_order' => 1,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 'cat-002-data-cables',
                'name' => 'Data Cables',
                'slug' => 'data-cables',
                'description' => 'Cables for data transfer between devices',
                'parent_id' => null,
                'lft' => 5,
                'rght' => 6,
                'icon' => 'transfer',
                'color' => '#007bff',
                'is_active' => 1,
                'sort_order' => 2,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ],
            [
                'id' => 'cat-003-video-adapters',
                'name' => 'Video Adapters',
                'slug' => 'video-adapters',
                'description' => 'Adapters for video signal conversion',
                'parent_id' => null,
                'lft' => 7,
                'rght' => 8,
                'icon' => 'display',
                'color' => '#6f42c1',
                'is_active' => 1,
                'sort_order' => 3,
                'created' => '2024-01-01 00:00:00',
                'modified' => '2024-01-01 00:00:00'
            ]
        ];
        parent::init();
    }
}
