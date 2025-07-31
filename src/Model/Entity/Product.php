<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Product Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $title
 * @property string|null $lede
 * @property string|null $body
 * @property string|null $summary
 * @property string|null $manufacturer
 * @property string|null $model_number
 * @property float|null $price
 * @property string $currency
 * @property float $reliability_score
 * @property string $entry_input_type
 * @property int $customer_peer_verification_count
 * @property string $verification_status
 * @property bool $is_published
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $published
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $tags
 * @property \App\Model\Entity\ProductCategory[] $categories
 * @property \App\Model\Entity\ProductReview[] $product_reviews
 * @property \App\Model\Entity\ProductSpecification[] $product_specifications
 * @property \App\Model\Entity\ProductConnector[] $product_connectors
 * @property \App\Model\Entity\ProductAffiliateLink[] $product_affiliate_links
 */
class Product extends Entity
{
    use SeoEntityTrait;
    use TranslateTrait;
    use ImageUrlTrait;

    protected array $_accessible = [
        'user_id' => true,
        'kind' => true,
        'featured' => true,
        'title' => true,
        'lede' => true,
        'slug' => true,
        'body' => true,
        'markdown' => true,
        'summary' => true,
        'image' => true,
        'manufacturer' => true,
        'model_number' => true,
        'product_code' => true,
        'price' => true,
        'currency' => true,
        'availability_status' => true,
        'stock_quantity' => true,
        'reliability_score' => true,
        'entry_input_type' => true,
        'customer_peer_verification_count' => true,
        'verification_status' => true,
        'verification_notes' => true,
        'technical_specs' => true,
        'connector_info' => true,
        'compatibility_info' => true,
        'is_published' => true,
        'published' => true,
        'created' => true,
        'modified' => true,
        'tags' => true,
        'categories' => true,
        'product_reviews' => true,
        'product_specifications' => true,
        'product_connectors' => true,
        'product_affiliate_links' => true,
        // SEO fields
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'twitter_description' => true,
        'instagram_description' => true,
    ];

    protected array $_hidden = [
        'verification_notes',
    ];
}
