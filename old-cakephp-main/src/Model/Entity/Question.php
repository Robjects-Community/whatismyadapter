<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Question Entity
 *
 * @property int $id
 * @property int $question_order_id
 * @property string $title
 * @property string|null $description
 *
 * @property \App\Model\Entity\QuestionOption[] $question_options
 * @property \App\Model\Entity\User[] $users
 */
class Question extends Entity
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
        'question_order_id' => true,
        'title' => true,
        'description' => true,
        'question_options' => true,
        'users' => true,
    ];
}
