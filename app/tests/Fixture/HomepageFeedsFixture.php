<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * HomepageFeedsFixture
 */
class HomepageFeedsFixture extends TestFixture
{
    public string $table = 'homepage_feeds';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'feed_type' => ['type' => 'string', 'length' => 50, 'null' => false],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'content' => ['type' => 'text', 'null' => true],
        'position' => ['type' => 'integer', 'null' => false, 'default' => 0],
        'is_active' => ['type' => 'boolean', 'null' => false, 'default' => true],
        'settings' => ['type' => 'text', 'null' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
        '_indexes' => [
            'feed_type_idx' => ['type' => 'index', 'columns' => ['feed_type']],
            'position_idx' => ['type' => 'index', 'columns' => ['position']],
            'active_idx' => ['type' => 'index', 'columns' => ['is_active']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-feed-1111-1111-111111111111',
                'feed_type' => 'featured_articles',
                'title' => 'Featured Articles',
                'content' => 'Displays featured articles on homepage',
                'position' => 1,
                'is_active' => true,
                'settings' => '{"limit": 3, "show_images": true}',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '22222222-feed-2222-2222-222222222222',
                'feed_type' => 'recent_products',
                'title' => 'Recent Products',
                'content' => 'Displays recently added products',
                'position' => 2,
                'is_active' => true,
                'settings' => '{"limit": 6, "show_prices": true}',
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
