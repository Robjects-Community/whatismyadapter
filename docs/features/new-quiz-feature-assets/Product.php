<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Product Entity
 *
 * @property int $id
 * @property string $title
 * @property string|null $manufacturer
 * @property string|null $port_type
 * @property string|null $form_factor
 * @property string|null $device_gender
 * @property string|null $device_cat
 * @property string|null $device_compatibility
 * @property float|null $price
 * @property float|null $rating
 * @property bool $certified
 * @property string $status
 * @property float|null $rel_score
 * @property int $views
 * @property string|null $image_url
 * @property array|null $features
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Product extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     */
    protected array $_accessible = [
        'title' => true,
        'manufacturer' => true,
        'port_type' => true,
        'form_factor' => true,
        'device_gender' => true,
        'device_cat' => true,
        'device_compatibility' => true,
        'price' => true,
        'rating' => true,
        'certified' => true,
        'status' => true,
        'rel_score' => true,
        'views' => true,
        'image_url' => true,
        'features' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Get formatted price with currency
     */
    public function getFormattedPrice(): string
    {
        if ($this->price === null) {
            return 'N/A';
        }
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get star rating display
     */
    public function getStarRating(): string
    {
        if ($this->rating === null) {
            return '';
        }
        $fullStars = floor($this->rating);
        $halfStar = $this->rating - $fullStars >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('★', (int)$fullStars) . 
               str_repeat('☆', $halfStar) . 
               str_repeat('☆', (int)$emptyStars);
    }

    /**
     * Check if product matches quiz filters
     */
    public function matchesFilters(array $filters): float
    {
        $score = 0.0;
        $totalChecks = 0;

        // Check manufacturer match
        if (!empty($filters['manufacturer'])) {
            $totalChecks++;
            if (in_array($this->manufacturer, (array)$filters['manufacturer'])) {
                $score += 0.3;
            }
        }

        // Check port type match
        if (!empty($filters['port_type'])) {
            $totalChecks++;
            if (in_array($this->port_type, (array)$filters['port_type'])) {
                $score += 0.25;
            }
        }

        // Check device category match
        if (!empty($filters['device_cat'])) {
            $totalChecks++;
            if (in_array($this->device_cat, (array)$filters['device_cat'])) {
                $score += 0.2;
            }
        }

        // Check price range
        if (!empty($filters['price_range']) && $this->price !== null) {
            $totalChecks++;
            $minPrice = $filters['price_range'][0] ?? 0;
            $maxPrice = $filters['price_range'][1] ?? 999999;
            if ($this->price >= $minPrice && $this->price <= $maxPrice) {
                $score += 0.15;
            }
        }

        // Check certification if required
        if (isset($filters['certified']) && $filters['certified']) {
            $totalChecks++;
            if ($this->certified) {
                $score += 0.1;
            }
        }

        return $totalChecks > 0 ? $score / $totalChecks : 0.0;
    }
}
?>