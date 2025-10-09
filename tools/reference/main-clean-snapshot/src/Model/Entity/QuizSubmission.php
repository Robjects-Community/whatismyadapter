<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Text;

/**
 * QuizSubmission Entity
 *
 * @property string $id
 * @property string|null $user_id
 * @property string $session_id
 * @property string $quiz_type
 * @property array $answers
 * @property array|null $matched_product_ids
 * @property array|null $confidence_scores
 * @property string|null $result_summary
 * @property array|null $analytics
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property string|null $created_by
 * @property string|null $modified_by
 *
 * @property \App\Model\Entity\User $user
 */
class QuizSubmission extends Entity
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
        'user_id' => true,
        'session_id' => true,
        'quiz_type' => true,
        'answers' => true,
        'matched_product_ids' => true,
        'confidence_scores' => true,
        'result_summary' => true,
        'analytics' => true,
        'created' => true,
        'modified' => true,
        'created_by' => true,
        'modified_by' => true,
        'user' => true,
    ];

    /**
     * JSON fields that should be automatically encoded/decoded
     *
     * @var array<string>
     */
    protected array $_jsonFields = [
        'answers',
        'matched_product_ids',
        'confidence_scores',
        'analytics',
    ];

    /**
     * Automatically generate UUID for new entities
     *
     * @param mixed $value The value to set
     * @return mixed|string The UUID or original value
     */
    protected function _setId(mixed $value): mixed
    {
        if (empty($value)) {
            return Text::uuid();
        }

        return $value;
    }

    /**
     * Get the confidence score as a percentage
     *
     * @return float|null
     */
    protected function _getConfidencePercentage(): ?float
    {
        if (!empty($this->confidence_scores) && is_array($this->confidence_scores)) {
            $overall = $this->confidence_scores['overall'] ?? null;
            if (is_numeric($overall)) {
                return round($overall * 100, 1);
            }
        }

        return null;
    }

    /**
     * Get a summary of answers for display
     *
     * @return array
     */
    protected function _getAnswersSummary(): array
    {
        if (!empty($this->answers) && is_array($this->answers)) {
            $summary = [];
            foreach ($this->answers as $key => $value) {
                if (is_array($value)) {
                    $summary[$key] = implode(', ', $value);
                } else {
                    $summary[$key] = (string)$value;
                }
            }

            return $summary;
        }

        return [];
    }
}
