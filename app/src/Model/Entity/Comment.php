<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Comment Entity
 *
 * @property string $id
 * @property string $foreign_key
 * @property string $model
 * @property string $user_id
 * @property string $content
 * @property bool $display
 * @property bool $is_inappropriate
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class Comment extends Entity
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
        'foreign_key' => true,
        'model' => true,
        'user_id' => true,
        'content' => true,
        'display' => true,
        'is_inappropriate' => false,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
