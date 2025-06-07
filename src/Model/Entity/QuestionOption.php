<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * QuestionOption Entity
 *
 * @property int $id
 * @property int $question_id
 * @property string $option_text
 * @property bool $is_correct
 *
 * @property \App\Model\Entity\Question $question
 */
class QuestionOption extends Entity
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
        'question_id' => true,
        'option_text' => true,
        'is_correct' => true,
        'question' => true,
    ];
}
