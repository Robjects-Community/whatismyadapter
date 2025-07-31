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
 * @property string $kind
 * @property bool $featured
 * @property string $title
 * @property string|null $lede
 * @property string $slug
 * @property string|null $body
 * @property string|null $markdown
 * @property string|null $summary
 * @property string|null $image
 * @property string|null $alt_text
 * @property string|null $keywords
 * @property string|null $name
 * @property string|null $dir
 * @property int|null $size
 * @property string|null $mime
 * @property bool|null $is_published
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $published
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $facebook_description
 * @property string|null $linkedin_description
 * @property string|null $instagram_description
 * @property string|null $twitter_description
 * @property int|null $word_count
 * @property string|null $parent_id
 * @property int $lft
 * @property int $rght
 * @property bool $main_menu
 * @property int $view_count
 * @property string|null $product_code
 * @property string|null $manufacturer
 * @property string|null $model_number
 * @property float|null $price
 * @property string|null $currency
 * @property string|null $availability_status
 * @property int|null $stock_quantity
 * @property float $reliability_score
 * @property string|null $entry_input_type
 * @property int $customer_peer_verification_count
 * @property string|null $verification_status
 * @property float|null $ai_analysis_score
 * @property string|null $verification_notes
 * @property \Cake\I18n\DateTime|null $verified_at
 * @property string|null $verified_by
 * @property array|null $technical_specs
 * @property array|null $connector_info
 * @property array|null $compatibility_info
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Product $parent_product
 * @property \App\Model\Entity\Product[] $child_products
 * @property \App\Model\Entity\Tag[] $tags
 * @property \App\Model\Entity\ProductCategory[] $categories
 * @property \App\Model\Entity\ProductSpecification[] $product_specifications
 * @property \App\Model\Entity\ProductConnector[] $product_connectors
 * @property \App\Model\Entity\ProductReview[] $product_reviews
 * @property \App\Model\Entity\ProductVerification[] $product_verifications
 * @property \App\Model\Entity\ProductAffiliateLink[] $product_affiliate_links
 * @property \App\Model\Entity\ProductView[] $product_views
 */
class Product extends Entity
{
    use SeoEntityTrait;
    use ImageUrlTrait;
    use TranslateTrait;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
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
        'alt_text' => true,
        'keywords' => true,
        'name' => true,
        'dir' => true,
        'size' => true,
        'mime' => true,
        'is_published' => true,
        'published' => true,
        'word_count' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'main_menu' => true,
        'view_count' => true,
        'product_code' => true,
        'manufacturer' => true,
        'model_number' => true,
        'price' => true,
        'currency' => true,
        'availability_status' => true,
        'stock_quantity' => true,
        'reliability_score' => true,
        'entry_input_type' => true,
        'customer_peer_verification_count' => true,
        'verification_status' => true,
        'ai_analysis_score' => true,
        'verification_notes' => true,
        'verified_at' => true,
        'verified_by' => true,
        'technical_specs' => true,
        'connector_info' => true,
        'compatibility_info' => true,
        'user' => true,
        'parent_product' => true,
        'child_products' => true,
        'tags' => true,
        'categories' => true,
        'product_specifications' => true,
        'product_connectors' => true,
        'product_reviews' => true,
        'product_verifications' => true,
        'product_affiliate_links' => true,
        'product_views' => true,
        // SEO fields (managed by SeoEntityTrait)
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'twitter_description' => true,
        'instagram_description' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'verification_notes',
    ];

    /**
     * Virtual field to check if product is verified
     */
    protected function _getIsVerified(): bool
    {
        return $this->verification_status === 'approved';
    }
}
