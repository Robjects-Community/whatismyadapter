<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SystemLog Entity
 *
 * @property string $id
 * @property string $level
 * @property string $message
 * @property string|null $context
 * @property \Cake\I18n\DateTime $created
 * @property string $group_name
 */
class SystemLog extends Entity
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
        'level' => true,
        'message' => true,
        'context' => true,
        'created' => true,
        'group_name' => true,
    ];
}
