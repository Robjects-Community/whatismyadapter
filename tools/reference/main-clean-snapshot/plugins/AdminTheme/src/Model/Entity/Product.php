<?php
declare(strict_types=1);

namespace App\Model\Entity;

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
 *
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Slug[] $slugs
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\ProductsTranslation[] $_i18n
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $tags
 * @property \App\Model\Entity\PageView[] $page_views
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
        'created' => true,
        'modified' => true,
        'published' => true,
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'instagram_description' => true,
        'twitter_description' => true,
        'word_count' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'main_menu' => true,
        'view_count' => true,
        'comments' => true,
        'slugs' => true,
        'images' => true,
        '_i18n' => true,
        'user' => true,
        'tags' => true,
        'page_views' => true,
    ];
}
