<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PageView Entity
 *
 * @property string $id
 * @property string $article_id
 * @property string $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\Article $article
 */
class PageView extends Entity
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
        'article_id' => true,
        'ip_address' => true,
        'user_agent' => true,
        'referer' => true,
        'created' => true,
        'article' => true,
    ];
}
