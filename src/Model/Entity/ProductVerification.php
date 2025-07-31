<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductVerification Entity
 *
 * @property string $id
 * @property string $product_id
 * @property string|null $verifier_user_id
 * @property string $verification_type
 * @property float $verification_score
 * @property array|null $verification_details
 * @property array|null $issues_found
 * @property array|null $suggestions
 * @property string|null $verification_notes
 * @property bool|null $is_approved
 * @property \Cake\I18n\DateTime|null $created
 *
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\User $user
 */
class ProductVerification extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'product_id' => true,
        'verifier_user_id' => true,
        'verification_type' => true,
        'verification_score' => true,
        'verification_details' => true,
        'issues_found' => true,
        'suggestions' => true,
        'verification_notes' => true,
        'is_approved' => true,
        'product' => true,
        'user' => true,
    ];
}
