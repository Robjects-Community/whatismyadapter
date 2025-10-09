<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceCompatibility Model - Logical junction table view for device compatibility
 *
 * This provides a normalized view of device compatibility data from the products table
 * for the prototype schema before actual database normalization.
 */
class DeviceCompatibilityTable extends Table
{
    /**
     * Initialize method
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('device_brand');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('device_category')
            ->maxLength('device_category', 50)
            ->allowEmptyString('device_category');

        $validator
            ->scalar('device_brand')
            ->maxLength('device_brand', 50)
            ->allowEmptyString('device_brand');

        $validator
            ->scalar('device_model')
            ->maxLength('device_model', 100)
            ->allowEmptyString('device_model');

        $validator
            ->scalar('compatibility_level')
            ->maxLength('compatibility_level', 20)
            ->allowEmptyString('compatibility_level');

        $validator
            ->decimal('performance_rating')
            ->allowEmptyString('performance_rating');

        $validator
            ->decimal('user_reported_rating')
            ->allowEmptyString('user_reported_rating');

        return $validator;
    }

    /**
     * Get distinct device categories
     */
    public function getDeviceCategories(): array
    {
        $cacheKey = 'device_categories';
        $categories = Cache::read($cacheKey);

        if ($categories === null) {
            $categories = $this->find()
                ->select(['device_category'])
                ->where(['device_category IS NOT' => null])
                ->distinct(['device_category'])
                ->orderBy(['device_category' => 'ASC'])
                ->toArray();

            $categories = array_column($categories, 'device_category');
            Cache::write($cacheKey, $categories, '1 hour');
        }

        return $categories;
    }

    /**
     * Get device brands by category
     */
    public function getBrandsByCategory(string $category): array
    {
        $cacheKey = "device_brands_{$category}";
        $brands = Cache::read($cacheKey);

        if ($brands === null) {
            $brands = $this->find()
                ->select(['device_brand'])
                ->where([
                    'device_category' => $category,
                    'device_brand IS NOT' => null,
                ])
                ->distinct(['device_brand'])
                ->orderBy(['device_brand' => 'ASC'])
                ->toArray();

            $brands = array_column($brands, 'device_brand');
            Cache::write($cacheKey, $brands, '1 hour');
        }

        return $brands;
    }

    /**
     * Get compatibility by device category
     */
    public function getCompatibilityByCategory(string $category): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'device_brand', 'device_model', 'compatibility_level',
                'performance_rating', 'user_reported_rating', 'compatibility_notes',
                'verification_date', 'verified_by',
            ])
            ->where([
                'device_category' => $category,
                'device_category IS NOT' => null,
            ])
            ->orderBy(['performance_rating' => 'DESC']);
    }

    /**
     * Get compatibility by brand and model
     */
    public function getCompatibilityByDevice(string $brand, ?string $model = null): Query
    {
        $conditions = [
            'device_brand' => $brand,
            'device_brand IS NOT' => null,
        ];

        if ($model) {
            $conditions['device_model LIKE'] = "%{$model}%";
        }

        return $this->find()
            ->select([
                'id', 'title', 'device_model', 'compatibility_level',
                'performance_rating', 'user_reported_rating', 'compatibility_notes',
                'verification_date',
            ])
            ->where($conditions)
            ->orderBy(['compatibility_level' => 'ASC', 'performance_rating' => 'DESC']);
    }

    /**
     * Get verified compatibility records
     */
    public function getVerifiedCompatibility(): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'device_category', 'device_brand', 'device_model',
                'compatibility_level', 'performance_rating', 'verified_by', 'verification_date',
            ])
            ->where([
                'verified_by IS NOT' => null,
                'verification_date IS NOT' => null,
                'device_category IS NOT' => null,
            ])
            ->orderBy(['verification_date' => 'DESC']);
    }

    /**
     * Get compatibility ratings analysis
     */
    public function getCompatibilityRatings(): Query
    {
        return $this->find()
            ->select([
                'device_category',
                'device_brand',
                'avg_performance' => 'AVG(performance_rating)',
                'avg_user_rating' => 'AVG(user_reported_rating)',
                'total_products' => 'COUNT(*)',
                'full_compatibility' => 'SUM(CASE WHEN compatibility_level = "Full" THEN 1 ELSE 0 END)',
                'partial_compatibility' => 'SUM(CASE WHEN compatibility_level = "Partial" THEN 1 ELSE 0 END)',
            ])
            ->where([
                'device_category IS NOT' => null,
                'device_brand IS NOT' => null,
            ])
            ->groupBy(['device_category', 'device_brand'])
            ->orderBy(['avg_performance' => 'DESC']);
    }

    /**
     * Get compatibility timeline
     */
    public function getCompatibilityTimeline(): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'device_category', 'device_brand', 'device_model',
                'compatibility_level', 'verification_date', 'performance_rating',
            ])
            ->where([
                'verification_date IS NOT' => null,
                'device_category IS NOT' => null,
            ])
            ->orderBy(['verification_date' => 'DESC'])
            ->limit(50);
    }

    /**
     * Search compatibility by performance rating
     */
    public function getByPerformanceRating(float $minRating): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'device_category', 'device_brand', 'device_model',
                'compatibility_level', 'performance_rating', 'user_reported_rating',
            ])
            ->where([
                'performance_rating >=' => $minRating,
                'device_category IS NOT' => null,
            ])
            ->orderBy(['performance_rating' => 'DESC']);
    }

    /**
     * Get compatibility statistics
     */
    public function getCompatibilityStats(): array
    {
        $cacheKey = 'compatibility_stats';
        $stats = Cache::read($cacheKey);

        if ($stats === null) {
            $stats = [
                'total_devices' => $this->find()
                    ->where(['device_category IS NOT' => null])
                    ->count(),

                'device_categories_count' => count($this->getDeviceCategories()),

                'verified_count' => $this->find()
                    ->where([
                        'verified_by IS NOT' => null,
                        'device_category IS NOT' => null,
                    ])
                    ->count(),

                'full_compatibility_count' => $this->find()
                    ->where([
                        'compatibility_level' => 'Full',
                        'device_category IS NOT' => null,
                    ])
                    ->count(),

                'average_performance' => $this->find()
                    ->where([
                        'performance_rating IS NOT' => null,
                        'device_category IS NOT' => null,
                    ])
                    ->select(['avg_perf' => 'AVG(performance_rating)'])
                    ->first()
                    ->avg_perf ?? 0,

                'average_user_rating' => $this->find()
                    ->where([
                        'user_reported_rating IS NOT' => null,
                        'device_category IS NOT' => null,
                    ])
                    ->select(['avg_user' => 'AVG(user_reported_rating)'])
                    ->first()
                    ->avg_user ?? 0,
            ];

            Cache::write($cacheKey, $stats, '30 minutes');
        }

        return $stats;
    }

    /**
     * Advanced compatibility search
     */
    public function advancedCompatibilitySearch(array $filters): Query
    {
        $query = $this->find()
            ->select([
                'id', 'title', 'device_category', 'device_brand', 'device_model',
                'compatibility_level', 'performance_rating', 'user_reported_rating',
                'compatibility_notes', 'verification_date',
            ])
            ->where(['device_category IS NOT' => null]);

        if (!empty($filters['device_category'])) {
            $query->where(['device_category' => $filters['device_category']]);
        }

        if (!empty($filters['device_brand'])) {
            $query->where(['device_brand LIKE' => '%' . $filters['device_brand'] . '%']);
        }

        if (!empty($filters['compatibility_level'])) {
            $query->where(['compatibility_level' => $filters['compatibility_level']]);
        }

        if (!empty($filters['min_performance'])) {
            $query->where(['performance_rating >=' => $filters['min_performance']]);
        }

        if (!empty($filters['verified_only'])) {
            $query->where(['verified_by IS NOT' => null]);
        }

        if (!empty($filters['search_notes'])) {
            $query->where(['compatibility_notes LIKE' => '%' . $filters['search_notes'] . '%']);
        }

        return $query->orderBy(['performance_rating' => 'DESC']);
    }

    /**
     * Clear compatibility-related caches
     */
    public function clearCompatibilityCache(): void
    {
        Cache::delete('device_categories');
        Cache::delete('compatibility_stats');

        // Clear category-specific caches
        foreach ($this->getDeviceCategories() as $category) {
            Cache::delete("device_brands_{$category}");
        }
    }
}
