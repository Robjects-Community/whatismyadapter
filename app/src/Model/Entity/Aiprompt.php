<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Aiprompt Entity
 *
 * @property string $id
 * @property string $task_type
 * @property string $system_prompt
 * @property string $model
 * @property int $max_tokens
 * @property float $temperature
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Aiprompt extends Entity
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
        'task_type' => true,
        'system_prompt' => true,
        'model' => true,
        'max_tokens' => true,
        'temperature' => true,
        'created' => true,
        'modified' => true,
    ];
}
