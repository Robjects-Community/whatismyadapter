<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use DateTime;

/**
 * ProductsReliabilityField Entity
 *
 * @property string $model
 * @property string $foreign_key
 * @property string $field
 * @property string $score
 * @property string $weight
 * @property string $max_score
 * @property string|null $notes
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class ProductsReliabilityField extends Entity
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
        'field' => true,
        'score' => true,
        'weight' => true,
        'max_score' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Get score as float
     *
     * @return float
     */
    protected function _getScoreFloat(): float
    {
        return (float)$this->score;
    }

    /**
     * Get weight as float
     *
     * @return float
     */
    protected function _getWeightFloat(): float
    {
        return (float)$this->weight;
    }

    /**
     * Get max score as float
     *
     * @return float
     */
    protected function _getMaxScoreFloat(): float
    {
        return (float)$this->max_score;
    }

    /**
     * Get weighted contribution to total score
     *
     * @return float
     */
    protected function _getWeightedContribution(): float
    {
        return $this->_getScoreFloat() * $this->_getWeightFloat();
    }

    /**
     * Get score as percentage
     *
     * @return float
     */
    protected function _getScorePercentage(): float
    {
        $maxScore = $this->_getMaxScoreFloat();
        if ($maxScore <= 0) {
            return 0.0;
        }

        return $this->_getScoreFloat() / $maxScore * 100;
    }

    /**
     * Check if this field has a perfect score
     *
     * @return bool
     */
    public function isPerfect(): bool
    {
        return $this->_getScoreFloat() >= $this->_getMaxScoreFloat();
    }

    /**
     * Check if this field is missing/empty (score = 0)
     *
     * @return bool
     */
    public function isMissing(): bool
    {
        return $this->_getScoreFloat() <= 0.0;
    }

    /**
     * Check if this field has a low score
     *
     * @param float $threshold Score threshold (0.0-1.0)
     * @return bool
     */
    public function isLowScore(float $threshold = 0.5): bool
    {
        return $this->_getScoreFloat() < $threshold;
    }

    /**
     * Get performance level classification
     *
     * @return string
     */
    protected function _getPerformanceLevel(): string
    {
        $score = $this->_getScoreFloat();

        if ($score >= 0.90) {
            return 'excellent';
        } elseif ($score >= 0.75) {
            return 'good';
        } elseif ($score >= 0.50) {
            return 'fair';
        } elseif ($score > 0.00) {
            return 'poor';
        } else {
            return 'missing';
        }
    }

    /**
     * Get display name for the field
     *
     * @return string
     */
    protected function _getFieldDisplayName(): string
    {
        // Convert snake_case to Title Case
        $words = explode('_', $this->field);
        $titleWords = array_map('ucfirst', $words);

        return implode(' ', $titleWords);
    }

    /**
     * Get CSS class for score badge based on performance
     *
     * @return string
     */
    protected function _getScoreBadgeClass(): string
    {
        switch ($this->performance_level) {
            case 'excellent':
                return 'badge bg-success';
            case 'good':
                return 'badge bg-info';
            case 'fair':
                return 'badge bg-warning';
            case 'poor':
                return 'badge bg-warning text-dark';
            case 'missing':
                return 'badge bg-danger';
            default:
                return 'badge bg-secondary';
        }
    }

    /**
     * Get icon class for performance level
     *
     * @return string
     */
    protected function _getPerformanceIcon(): string
    {
        switch ($this->performance_level) {
            case 'excellent':
                return 'fas fa-check-circle text-success';
            case 'good':
                return 'fas fa-thumbs-up text-info';
            case 'fair':
                return 'fas fa-exclamation-triangle text-warning';
            case 'poor':
                return 'fas fa-times-circle text-warning';
            case 'missing':
                return 'fas fa-ban text-danger';
            default:
                return 'fas fa-question-circle text-secondary';
        }
    }

    /**
     * Get human-readable score summary
     *
     * @return string
     */
    protected function _getScoreSummary(): string
    {
        $score = $this->_getScoreFloat();
        $percentage = $this->_getScorePercentage();
        $level = ucfirst($this->performance_level);

        return sprintf('%s: %.2f/%.2f (%.1f%%)', $level, $score, $this->_getMaxScoreFloat(), $percentage);
    }

    /**
     * Check if the field score was recently updated
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
