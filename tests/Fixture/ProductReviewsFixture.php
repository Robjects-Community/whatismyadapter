<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductReviewsFixture
 */
class ProductReviewsFixture extends TestFixture
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
                'id' => 'review-001',
                'product_id' => 'prod-001-usb-c-cable',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'rating' => 5,
                'title' => 'Excellent Cable Quality',
                'review_text' => 'This USB-C to Lightning cable works perfectly with my iPhone. Fast charging and data transfer work as expected. Build quality feels premium.',
                'verified_purchase' => 1,
                'is_approved' => 1,
                'is_featured' => 1,
                'helpful_votes' => 12,
                'unhelpful_votes' => 1,
                'moderation_notes' => null,
                'created' => '2024-01-20 14:30:00',
                'modified' => '2024-01-20 14:30:00'
            ],
            [
                'id' => 'review-002',
                'product_id' => 'prod-001-usb-c-cable',
                'user_id' => '199b7544-8725-49ee-a26c-a3f32e03e423',
                'rating' => 4,
                'title' => 'Good Value for Money',
                'review_text' => 'Works well for daily use. Charging speed is good but not the fastest I\'ve seen. Cable feels sturdy.',
                'verified_purchase' => 1,
                'is_approved' => 1,
                'is_featured' => 0,
                'helpful_votes' => 8,
                'unhelpful_votes' => 2,
                'moderation_notes' => null,
                'created' => '2024-01-22 16:45:00',
                'modified' => '2024-01-22 16:45:00'
            ],
            [
                'id' => 'review-003',
                'product_id' => 'prod-002-hdmi-adapter',
                'user_id' => '299b7544-8725-49ee-a26c-a3f32e03e424',
                'rating' => 5,
                'title' => 'Perfect 4K Performance',
                'review_text' => 'Crystal clear 4K output to my monitor. No lag or quality issues. Plug and play functionality works great.',
                'verified_purchase' => 1,
                'is_approved' => 1,
                'is_featured' => 1,
                'helpful_votes' => 15,
                'unhelpful_votes' => 0,
                'moderation_notes' => null,
                'created' => '2024-01-25 11:20:00',
                'modified' => '2024-01-25 11:20:00'
            ],
            [
                'id' => 'review-004',
                'product_id' => 'prod-002-hdmi-adapter',
                'user_id' => '399b7544-8725-49ee-a26c-a3f32e03e425',
                'rating' => 3,
                'title' => 'Decent but Gets Warm',
                'review_text' => 'Works for basic use but gets quite warm during extended sessions. Picture quality is acceptable.',
                'verified_purchase' => 0,
                'is_approved' => 1,
                'is_featured' => 0,
                'helpful_votes' => 4,
                'unhelpful_votes' => 3,
                'moderation_notes' => 'Non-verified purchase but helpful review',
                'created' => '2024-01-28 09:15:00',
                'modified' => '2024-01-28 09:15:00'
            ],
            [
                'id' => 'review-005',
                'product_id' => 'prod-001-usb-c-cable',
                'user_id' => '499b7544-8725-49ee-a26c-a3f32e03e426',
                'rating' => 2,
                'title' => 'Stopped Working After 3 Months',
                'review_text' => 'Initial quality seemed good but cable stopped charging properly after 3 months of regular use.',
                'verified_purchase' => 1,
                'is_approved' => 0,
                'is_featured' => 0,
                'helpful_votes' => 3,
                'unhelpful_votes' => 8,
                'moderation_notes' => 'Under review - potential counterfeit product',
                'created' => '2024-02-01 13:10:00',
                'modified' => '2024-02-01 13:10:00'
            ]
        ];
        parent::init();
    }
}
