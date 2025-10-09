<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CableCapabilities Model - Logical junction table view for cable capabilities
 *
 * This provides a normalized view of cable capability data from the products table
 * for the prototype schema before actual database normalization.
 */
class CableCapabilitiesTable extends Table
{
    /**
     * Initialize method
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products'); // Uses products table but provides logical view
        $this->setDisplayField('capability_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Virtual association back to Products
        $this->belongsTo('Products', [
            'foreignKey' => 'id',
            'propertyName' => 'product',
        ]);
    }

    /**
     * Default validation rules
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('capability_name')
            ->maxLength('capability_name', 100)
            ->allowEmptyString('capability_name');

        $validator
            ->scalar('capability_category')
            ->maxLength('capability_category', 50)
            ->allowEmptyString('capability_category');

        $validator
            ->scalar('testing_standard')
            ->maxLength('testing_standard', 255)
            ->allowEmptyString('testing_standard');

        $validator
            ->scalar('certifying_organization')
            ->maxLength('certifying_organization', 100)
            ->allowEmptyString('certifying_organization');

        $validator
            ->decimal('numeric_rating')
            ->allowEmptyString('numeric_rating');

        $validator
            ->boolean('is_certified')
            ->notEmptyString('is_certified');

        return $validator;
    }

    /**
     * Get distinct capability categories
     */
    public function getCapabilityCategories(): array
    {
        $cacheKey = 'capability_categories';
        $categories = Cache::read($cacheKey);

        if ($categories === null) {
            $categories = $this->find()
                ->select(['capability_category'])
                ->where(['capability_category IS NOT' => null])
                ->distinct(['capability_category'])
                ->orderBy(['capability_category' => 'ASC'])
                ->toArray();

            $categories = array_column($categories, 'capability_category');
            Cache::write($cacheKey, $categories, '1 hour');
        }

        return $categories;
    }

    /**
     * Get capabilities by category
     */
    public function getCapabilitiesByCategory(string $category): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'capability_name', 'capability_value',
                'numeric_rating', 'is_certified', 'certification_date',
                'testing_standard', 'certifying_organization',
            ])
            ->where([
                'capability_category' => $category,
                'capability_name IS NOT' => null,
            ])
            ->orderBy(['numeric_rating' => 'DESC']);
    }

    /**
     * Get certified capabilities only
     */
    public function getCertifiedCapabilities(): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'capability_name', 'capability_category',
                'certifying_organization', 'certification_date', 'numeric_rating',
            ])
            ->where([
                'is_certified' => true,
                'capability_name IS NOT' => null,
                'certifying_organization IS NOT' => null,
            ])
            ->orderBy(['certification_date' => 'DESC']);
    }

    /**
     * Get capability statistics
     */
    public function getCapabilityStats(): array
    {
        $cacheKey = 'capability_stats';
        $stats = Cache::read($cacheKey);

        if ($stats === null) {
            $stats = [
                'total_capabilities' => $this->find()
                    ->where(['capability_name IS NOT' => null])
                    ->count(),

                'certified_count' => $this->find()
                    ->where([
                        'is_certified' => true,
                        'capability_name IS NOT' => null,
                    ])
                    ->count(),

                'average_rating' => $this->find()
                    ->where([
                        'numeric_rating IS NOT' => null,
                        'capability_name IS NOT' => null,
                    ])
                    ->select(['avg_rating' => 'AVG(numeric_rating)'])
                    ->first()
                    ->avg_rating ?? 0,

                'categories_count' => count($this->getCapabilityCategories()),
            ];

            Cache::write($cacheKey, $stats, '30 minutes');
        }

        return $stats;
    }

    /**
     * Search capabilities by technical specifications
     */
    public function searchByTechnicalSpecs(string $searchTerm): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'capability_name', 'capability_category',
                'technical_specifications', 'numeric_rating',
            ])
            ->where([
                'OR' => [
                    'JSON_EXTRACT(technical_specifications, "$.description") LIKE' => "%{$searchTerm}%",
                    'capability_name LIKE' => "%{$searchTerm}%",
                    'testing_standard LIKE' => "%{$searchTerm}%",
                ],
                'capability_name IS NOT' => null,
            ])
            ->orderBy(['numeric_rating' => 'DESC']);
    }

    /**
     * Clear capability-related caches
     */
    public function clearCapabilityCache(): void
    {
        Cache::delete('capability_categories');
        Cache::delete('capability_stats');
    }
}
