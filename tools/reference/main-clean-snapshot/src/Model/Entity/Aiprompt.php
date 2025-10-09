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
 * @property string|null $status
 * @property \Cake\I18n\DateTime|null $last_used
 * @property int $usage_count
 * @property float|null $success_rate
 * @property string|null $description
 * @property string|null $preview_sample
 * @property string|null $expected_output
 * @property bool $is_active
 * @property string|null $category
 * @property string|null $version
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
        'status' => true,
        'last_used' => true,
        'usage_count' => true,
        'success_rate' => true,
        'description' => true,
        'preview_sample' => true,
        'expected_output' => true,
        'is_active' => true,
        'category' => true,
        'version' => true,
        'created' => true,
        'modified' => true,
    ];
}
