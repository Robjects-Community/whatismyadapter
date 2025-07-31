<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductReview Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string $user_id
 * @property int $rating
 * @property string|null $title
 * @property string|null $review_text
 * @property bool $verified_purchase
 * @property bool $is_approved
 * @property bool $is_featured
 * @property int $helpful_votes
 * @property int $unhelpful_votes
 * @property string|null $moderation_notes
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\User $user
 */
class ProductReview extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'user_id' => true,
        'rating' => true,
        'title' => true,
        'review_text' => true,
        'verified_purchase' => true,
        'is_approved' => true,
        'is_featured' => true,
        'helpful_votes' => true,
        'unhelpful_votes' => true,
        'moderation_notes' => true,
        'product' => true,
        'user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'moderation_notes',
    ];
}
