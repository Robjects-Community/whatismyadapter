<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductAffiliateLink Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string $affiliate_network
 * @property string $merchant_name
 * @property string $affiliate_url
 * @property float|null $price
 * @property string|null $currency
 * @property float|null $commission_rate
 * @property bool $is_primary
 * @property bool $is_active
 * @property int $click_count
 * @property int $conversion_count
 * @property \Cake\I18n\DateTime|null $last_checked
 * @property string|null $availability_status
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Product $product
 */
class ProductAffiliateLink extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'affiliate_network' => true,
        'merchant_name' => true,
        'affiliate_url' => true,
        'price' => true,
        'currency' => true,
        'commission_rate' => true,
        'is_primary' => true,
        'is_active' => true,
        'click_count' => true,
        'conversion_count' => true,
        'last_checked' => true,
        'availability_status' => true,
        'product' => true,
    ];
}
