<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductView Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\Product $product
 */
class ProductView extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'ip_address' => true,
        'user_agent' => true,
        'referer' => true,
        'product' => true,
    ];
}
