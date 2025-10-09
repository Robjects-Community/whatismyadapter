<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use DateInterval;
use DateTime;

/**
 * ProductsReliabilityLog Entity
 *
 * @property string $id
 * @property string $model
 * @property string $foreign_key
 * @property string|null $from_total_score
 * @property string $to_total_score
 * @property string|null $from_field_scores_json
 * @property string $to_field_scores_json
 * @property string $source
 * @property string|null $actor_user_id
 * @property string|null $actor_service
 * @property string|null $message
 * @property string $checksum_sha256
 * @property \Cake\I18n\DateTime $created
 */
class ProductsReliabilityLog extends Entity
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
        'from_total_score' => true,
        'to_total_score' => true,
        'from_field_scores_json' => true,
        'to_field_scores_json' => true,
        'source' => true,
        'actor_user_id' => true,
        'actor_service' => true,
        'message' => true,
        'checksum_sha256' => true,
        'created' => true,
    ];

    /**
     * Get from score as float
     *
     * @return float|null
     */
    protected function _getFromTotalScoreFloat(): ?float
    {
        return $this->from_total_score !== null ? (float)$this->from_total_score : null;
    }

    /**
     * Get to score as float
     *
     * @return float
     */
    protected function _getToTotalScoreFloat(): float
    {
        return (float)$this->to_total_score;
    }

    /**
     * Get score delta (change amount)
     *
     * @return float|null
     */
    protected function _getScoreDelta(): ?float
    {
        if ($this->from_total_score === null) {
            return null; // Initial score, no delta
        }

        return $this->_getToTotalScoreFloat() - $this->_getFromTotalScoreFloat();
    }

    /**
     * Get from field scores as decoded array
     *
     * @return array|null
     */
    protected function _getFromFieldScoresArray(): ?array
    {
        if ($this->from_field_scores_json === null) {
            return null;
        }

        $decoded = json_decode($this->from_field_scores_json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get to field scores as decoded array
     *
     * @return array|null
     */
    protected function _getToFieldScoresArray(): ?array
    {
        if ($this->to_field_scores_json === null) {
            return null;
        }

        $decoded = json_decode($this->to_field_scores_json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Check if this is the initial score (no previous score)
     *
     * @return bool
     */
    public function isInitialScore(): bool
    {
        return $this->from_total_score === null;
    }

    /**
     * Check if the score improved
     *
     * @param float $threshold Minimum improvement to consider significant
     * @return bool
     */
    public function isImprovement(float $threshold = 0.01): bool
    {
        $delta = $this->score_delta;

        return $delta !== null && $delta > $threshold;
    }

    /**
     * Check if the score degraded
     *
     * @param float $threshold Minimum degradation to consider significant
     * @return bool
     */
    public function isDegradation(float $threshold = 0.01): bool
    {
        $delta = $this->score_delta;

        return $delta !== null && $delta < -$threshold;
    }

    /**
     * Check if this represents a significant change
     *
     * @param float $threshold Minimum change to consider significant
     * @return bool
     */
    public function isSignificantChange(float $threshold = 0.10): bool
    {
        $delta = $this->score_delta;

        return $delta !== null && abs($delta) >= $threshold;
    }

    /**
     * Get the change type classification
     *
     * @return string 'initial', 'improvement', 'degradation', or 'no_change'
     */
    protected function _getChangeType(): string
    {
        if ($this->isInitialScore()) {
            return 'initial';
        }

        $delta = $this->score_delta;
        if ($delta > 0.01) {
            return 'improvement';
        } elseif ($delta < -0.01) {
            return 'degradation';
        } else {
            return 'no_change';
        }
    }

    /**
     * Get source display name
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

        return $sourceMap[$this->source] ?? ucfirst($this->source);
    }

    /**
     * Get actor display name (user or service)
     *
     * @return string
     */
    protected function _getActorDisplay(): string
    {
        if ($this->actor_user_id !== null) {
            return "User {$this->actor_user_id}";
        }

        if ($this->actor_service !== null) {
            return ucfirst($this->actor_service);
        }

        return 'System';
    }

    /**
     * Get CSS class for change type badge
     *
     * @return string
     */
    protected function _getChangeBadgeClass(): string
    {
        switch ($this->change_type) {
            case 'initial':
                return 'badge bg-info';
            case 'improvement':
                return 'badge bg-success';
            case 'degradation':
                return 'badge bg-danger';
            case 'no_change':
                return 'badge bg-secondary';
            default:
                return 'badge bg-light';
        }
    }

    /**
     * Get icon for change type
     *
     * @return string
     */
    protected function _getChangeIcon(): string
    {
        switch ($this->change_type) {
            case 'initial':
                return 'fas fa-plus-circle';
            case 'improvement':
                return 'fas fa-arrow-up';
            case 'degradation':
                return 'fas fa-arrow-down';
            case 'no_change':
                return 'fas fa-minus';
            default:
                return 'fas fa-question';
        }
    }

    /**
     * Get human-readable summary of the change
     *
     * @return string
     */
    protected function _getChangeSummary(): string
    {
        if ($this->isInitialScore()) {
            return sprintf('Initial score: %.2f', $this->_getToTotalScoreFloat());
        }

        $delta = $this->score_delta;
        $fromScore = $this->_getFromTotalScoreFloat();
        $toScore = $this->_getToTotalScoreFloat();

        if ($delta > 0) {
            return sprintf('Improved: %.2f → %.2f (+%.2f)', $fromScore, $toScore, $delta);
        } elseif ($delta < 0) {
            return sprintf('Declined: %.2f → %.2f (%.2f)', $fromScore, $toScore, $delta);
        } else {
            return sprintf('No change: %.2f', $toScore);
        }
    }

    /**
     * Check if the log entry was created recently
     *
     * @param int $hours Number of hours to consider "recent"
     * @return bool
     */
    public function isRecent(int $hours = 24): bool
    {
        if ($this->created === null) {
            return false;
        }

        $cutoff = new DateTime("-{$hours} hours");

        return $this->created >= $cutoff;
    }

    /**
     * Get time elapsed since creation
     *
     * @return \DateInterval|null
     */
    protected function _getTimeElapsed(): ?DateInterval
    {
        if ($this->created === null) {
            return null;
        }

        $now = new DateTime();

        return $now->diff($this->created);
    }

    /**
     * Get human-readable time elapsed string
     *
     * @return string
     */
    protected function _getTimeElapsedString(): string
    {
        $interval = $this->time_elapsed;
        if ($interval === null) {
            return 'Unknown';
        }

        if ($interval->days > 0) {
            return $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
}
