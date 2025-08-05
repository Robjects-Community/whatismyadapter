<?php
declare(strict_types=1);

namespace App\Model\Entity;

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
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Article $article
 * @property \App\Model\Entity\Tag[] $tags
 */
class Product extends Entity
{
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
    ];
}
