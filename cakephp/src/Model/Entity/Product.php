<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Product Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $article_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $manufacturer
 * @property string|null $model_number
 * @property string|null $price
 * @property string|null $currency
 * @property string|null $image
 * @property string|null $alt_text
 * @property bool $is_published
 * @property bool $featured
 * @property string $verification_status
 * @property string|null $reliability_score
 * @property int $view_count
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Article $article
 * @property \App\Model\Entity\Tag[] $tags
 */
class Product extends Entity
{
    use SeoEntityTrait;
    use TranslateTrait;
    use ImageUrlTrait;

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
        'article_id' => true,
        'title' => true,
        'slug' => true,
        'description' => true,
        'manufacturer' => true,
        'model_number' => true,
        'price' => true,
        'currency' => true,
        'image' => true,
        'alt_text' => true,
        'is_published' => true,
        'featured' => true,
        'verification_status' => true,
        'reliability_score' => true,
        'view_count' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'article' => true,
        'tags' => true,
        'products_reliability' => true,
        // Allow mass assignment for SEO fields
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'twitter_description' => true,
        'instagram_description' => true,
    ];

    /**
     * Virtual reliability score getter for backward compatibility
     *
     * Returns the reliability score from the ProductsReliability association
     * when available, otherwise falls back to the legacy column value.
     *
     * @return float|null
     */
    protected function _getReliabilityScoreCalculated(): ?float
    {
        // If we have the ProductsReliability association loaded, use that
        if ($this->has('products_reliability') && $this->products_reliability !== null) {
            return $this->products_reliability->total_score_float;
        }

        // Fall back to the legacy column value
        if ($this->has('reliability_score') && $this->reliability_score !== null) {
            return (float)$this->reliability_score;
        }

        return null;
    }

    /**
     * Check if the provided user owns this product.
     *
     * @param \App\Model\Entity\User|\Cake\Datasource\EntityInterface|array|string $user User entity or user id
     * @return bool
     */
    public function isOwnedBy(User|string|array|EntityInterface $user): bool
    {
        $userId = null;

        if (is_string($user)) {
            $userId = $user;
        } elseif (is_array($user) && isset($user['id'])) {
            $userId = (string)$user['id'];
        } elseif (is_object($user)) {
            if (method_exists($user, 'get')) {
                $userId = (string)$user->get('id');
            } elseif (property_exists($user, 'id')) {
                $userId = (string)$user->id;
            }
        }

        if ($userId === null) {
            return false;
        }

        $productUserId = (string)($this->get('user_id') ?? $this->user_id ?? '');

        return $productUserId !== '' && $productUserId === (string)$userId;
    }
}
