<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\AiMetric;
use Authorization\IdentityInterface;

/**
 * AiMetric Policy
 * 
 * Defines authorization rules for AI metrics
 * Only administrators can access AI metrics
 */
class AiMetricPolicy
{
    /**
     * Check if user can index AI metrics
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canIndex(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can view AI metrics
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can view an AI metric
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canView(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can view AI metrics
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can add an AI metric
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can add AI metrics
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can edit an AI metric
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can edit AI metrics
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can delete an AI metric
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can delete AI metrics
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can access the dashboard
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canDashboard(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can access the dashboard
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Check if user can access realtime data
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\AiMetric $aiMetric The AI metric.
     * @return bool
     */
    public function canRealtimeData(IdentityInterface $user, AiMetric $aiMetric): bool
    {
        // Only admin users can access realtime data
        return $user->canAccessAdmin() && $user->is_admin;
    }

    /**
     * Scope the index query - admins see all metrics
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query $query The query.
     * @return \Cake\ORM\Query
     */
    public function scopeIndex(IdentityInterface $user, $query)
    {
        // Admins see all AI metrics
        return $query;
    }
}
