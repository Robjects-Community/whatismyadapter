<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CookieConsent Entity
 *
 * @property string $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property bool $analytics_consent
 * @property bool $functional_consent
 * @property bool $marketing_consent
 * @property bool $essential_consent
 * @property string $ip_address
 * @property string $user_agent
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $updated
 *
 * @property \App\Model\Entity\User $user
 */
class CookieConsent extends Entity
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
        'analytics_consent' => true,
        'functional_consent' => true,
        'marketing_consent' => true,
        'essential_consent' => true,
        'ip_address' => true,
        'user_agent' => true,
        'created' => true,
        'updated' => true,
    ];

    /**
     * Check if analytics cookies are allowed.
     *
     * @return bool
     */
    public function hasAnalyticsConsent(): bool
    {
        return (bool)$this->analytics_consent;
    }

    /**
     * Check if functional cookies are allowed.
     *
     * @return bool
     */
    public function hasFunctionalConsent(): bool
    {
        return (bool)$this->functional_consent;
    }

    /**
     * Check if marketing cookies are allowed.
     *
     * @return bool
     */
    public function hasMarketingConsent(): bool
    {
        return (bool)$this->marketing_consent;
    }
}
