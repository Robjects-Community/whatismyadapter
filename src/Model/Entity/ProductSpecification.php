<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductSpecification Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string $spec_category
 * @property string $spec_name
 * @property string $spec_value
 * @property string|null $spec_unit
 * @property string|null $spec_type
 * @property int $display_order
 * @property bool $is_filterable
 * @property bool $is_searchable
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Product $product
 */
class ProductSpecification extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'spec_category' => true,
        'spec_name' => true,
        'spec_value' => true,
        'spec_unit' => true,
        'spec_type' => true,
        'display_order' => true,
        'is_filterable' => true,
        'is_searchable' => true,
        'product' => true,
    ];
}
