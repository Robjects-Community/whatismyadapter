<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use DateTime;

/**
 * ProductsReliability Entity
 *
 * @property string $id
 * @property string $model
 * @property string $foreign_key
 * @property string $total_score
 * @property string $completeness_percent
 * @property string|null $field_scores_json
 * @property string $scoring_version
 * @property string $last_source
 * @property \Cake\I18n\DateTime|null $last_calculated
 * @property string|null $updated_by_user_id
 * @property string|null $updated_by_service
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class ProductsReliability extends Entity
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
        'model' => true,
        'foreign_key' => true,
        'total_score' => true,
        'completeness_percent' => true,
        'field_scores_json' => true,
        'scoring_version' => true,
        'last_source' => true,
        'last_calculated' => true,
        'updated_by_user_id' => true,
        'updated_by_service' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Get field scores as decoded array
     *
     * @return array|null
     */
    protected function _getFieldScoresArray(): ?array
    {
        if ($this->field_scores_json === null) {
            return null;
        }

        $decoded = json_decode($this->field_scores_json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get total score as float
     *
     * @return float
     */
    protected function _getTotalScoreFloat(): float
    {
        return (float)$this->total_score;
    }

    /**
     * Get completeness percent as float
     *
     * @return float
     */
    protected function _getCompletenessPercentFloat(): float
    {
        return (float)$this->completeness_percent;
    }

    /**
     * Get a human-readable summary of the reliability score
     *
     * @return string
     */
    protected function _getScoreSummary(): string
    {
        $score = $this->_getTotalScoreFloat();
        $completeness = $this->_getCompletenessPercentFloat();

        if ($score >= 0.90) {
            $rating = 'Excellent';
        } elseif ($score >= 0.75) {
            $rating = 'Good';
        } elseif ($score >= 0.50) {
            $rating = 'Fair';
        } else {
            $rating = 'Poor';
        }

        return sprintf('%s (%.2f/1.00, %.1f%% complete)', $rating, $score, $completeness);
    }

    /**
     * Get badge CSS class based on score
     *
     * @return string
     */
    protected function _getBadgeClass(): string
    {
        $score = $this->_getTotalScoreFloat();

        if ($score >= 0.90) {
            return 'badge bg-success'; // Green
        } elseif ($score >= 0.75) {
            return 'badge bg-info'; // Blue
        } elseif ($score >= 0.50) {
            return 'badge bg-warning'; // Yellow
        } else {
            return 'badge bg-danger'; // Red
        }
    }

    /**
     * Check if this record needs attention (low score or completeness)
     *
     * @param float $minScore Minimum acceptable score
     * @param float $minCompleteness Minimum acceptable completeness percentage
     * @return bool
     */
    public function needsAttention(float $minScore = 0.70, float $minCompleteness = 80.0): bool
    {
        return $this->_getTotalScoreFloat() < $minScore ||
               $this->_getCompletenessPercentFloat() < $minCompleteness;
    }

    /**
     * Get the source display name
     *
     * @return string
     */
    protected function _getSourceDisplay(): string
    {
        $sourceMap = [
            'user' => 'User Update',
            'ai' => 'AI Analysis',
            'admin' => 'Admin Review',
            'system' => 'System Process',
        ];

        return $sourceMap[$this->last_source] ?? ucfirst($this->last_source);
    }

    /**
     * Check if the score was recently updated
     *
     * @param int $hours Number of hours to consider "recent"
     * @return bool
     */
    public function isRecentlyUpdated(int $hours = 24): bool
    {
        if ($this->modified === null) {
            return false;
        }

        $cutoff = new DateTime("-{$hours} hours");

        return $this->modified >= $cutoff;
    }
}
