<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductConnector Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string $connector_type_id
 * @property string $connector_role
 * @property string|null $connector_position
 * @property int $quantity
 * @property float|null $power_rating_watts
 * @property float|null $data_speed_gbps
 * @property string|null $notes
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\ConnectorType $connector_type
 */
class ProductConnector extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'connector_type_id' => true,
        'connector_role' => true,
        'connector_position' => true,
        'quantity' => true,
        'power_rating_watts' => true,
        'data_speed_gbps' => true,
        'notes' => true,
        'product' => true,
        'connector_type' => true,
    ];
}
