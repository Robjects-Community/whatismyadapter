<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ConnectorType Entity
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $display_name
 * @property string|null $description
 * @property string|null $connector_family
 * @property bool|null $is_input
 * @property bool|null $is_output
 * @property bool|null $power_capability
 * @property bool|null $data_capability
 * @property bool|null $video_capability
 * @property bool|null $audio_capability
 * @property float|null $max_power_watts
 * @property float|null $max_data_speed_gbps
 * @property string|null $icon
 * @property bool $is_active
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\ProductConnector[] $product_connectors
 */
class ConnectorType extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'display_name' => true,
        'description' => true,
        'connector_family' => true,
        'is_input' => true,
        'is_output' => true,
        'power_capability' => true,
        'data_capability' => true,
        'video_capability' => true,
        'audio_capability' => true,
        'max_power_watts' => true,
        'max_data_speed_gbps' => true,
        'icon' => true,
        'is_active' => true,
        'product_connectors' => true,
    ];
}
