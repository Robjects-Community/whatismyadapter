<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductCategory Entity
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $parent_id
 * @property int $lft
 * @property int $rght
 * @property string|null $icon
 * @property string|null $color
 * @property bool $is_active
 * @property int $sort_order
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\ProductCategory $parent_product_category
 * @property \App\Model\Entity\ProductCategory[] $child_product_categories
 * @property \App\Model\Entity\Product[] $products
 */
class ProductCategory extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'description' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'icon' => true,
        'color' => true,
        'is_active' => true,
        'sort_order' => true,
        'parent_product_category' => true,
        'child_product_categories' => true,
        'products' => true,
    ];
}
