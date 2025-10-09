<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property string $id
 * @property string $category
 * @property string $key_name
 * @property string|null $value
 * @property string $type
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class Setting extends Entity
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
        'ordering' => true,
        'category' => true,
        'key_name' => true,
        'value' => true,
        'type' => true,
        'value_type' => true,
        'value_obscure' => true,
        'description' => true,
        'data' => true,
        'column_width' => true,
        'created' => true,
        'modified' => true,
    ];
}
