<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BlockedIp Entity
 *
 * @property string $id
 * @property string $ip_address
 * @property string|null $reason
 * @property \Cake\I18n\DateTime $blocked_at
 * @property \Cake\I18n\DateTime|null $expires_at
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class BlockedIp extends Entity
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
        'ip_address' => true,
        'reason' => true,
        'blocked_at' => true,
        'expires_at' => true,
        'created' => true,
        'modified' => true,
    ];
}
