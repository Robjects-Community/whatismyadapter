<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AiMetric Entity
 *
 * @property string $id
 * @property string $task_type
 * @property int|null $execution_time_ms
 * @property int|null $tokens_used
 * @property string|null $cost_usd
 * @property bool $success
 * @property string|null $error_message
 * @property string|null $model_used
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class AiMetric extends Entity
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
        'execution_time_ms' => true,
        'tokens_used' => true,
        'cost_usd' => true,
        'success' => true,
        'error_message' => true,
        'model_used' => true,
        'created' => true,
        'modified' => true,
    ];
}
