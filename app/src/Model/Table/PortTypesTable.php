<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PortTypes Model - Logical junction table view for port types
 *
 * This provides a normalized view of port type data from the products table
 * for the prototype schema before actual database normalization.
 */
class PortTypesTable extends Table
{
    /**
     * Initialize method
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('port_type_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('port_family')
            ->maxLength('port_family', 50)
            ->allowEmptyString('port_family');

        $validator
            ->scalar('form_factor')
            ->maxLength('form_factor', 30)
            ->allowEmptyString('form_factor');

        $validator
            ->scalar('connector_gender')
            ->maxLength('connector_gender', 15)
            ->allowEmptyString('connector_gender');

        $validator
            ->integer('pin_count')
            ->allowEmptyString('pin_count');

        $validator
            ->decimal('max_voltage')
            ->allowEmptyString('max_voltage');

        $validator
            ->decimal('max_current')
            ->allowEmptyString('max_current');

        return $validator;
    }

    /**
     * Get distinct port families
     */
    public function getPortFamilies(): array
    {
        $cacheKey = 'port_families';
        $families = Cache::read($cacheKey);

        if ($families === null) {
            $families = $this->find()
                ->select(['port_family'])
                ->where(['port_family IS NOT' => null])
                ->distinct(['port_family'])
                ->orderBy(['port_family' => 'ASC'])
                ->toArray();

            $families = array_column($families, 'port_family');
            Cache::write($cacheKey, $families, '1 hour');
        }

        return $families;
    }

    /**
     * Get form factors by port family
     */
    public function getFormFactorsByFamily(string $family): array
    {
        $cacheKey = "form_factors_{$family}";
        $formFactors = Cache::read($cacheKey);

        if ($formFactors === null) {
            $formFactors = $this->find()
                ->select(['form_factor'])
                ->where([
                    'port_family' => $family,
                    'form_factor IS NOT' => null,
                ])
                ->distinct(['form_factor'])
                ->orderBy(['form_factor' => 'ASC'])
                ->toArray();

            $formFactors = array_column($formFactors, 'form_factor');
            Cache::write($cacheKey, $formFactors, '1 hour');
        }

        return $formFactors;
    }

    /**
     * Get ports by family and form factor
     */
    public function getPortsByFamilyAndForm(string $family, ?string $formFactor = null): Query
    {
        $conditions = [
            'port_family' => $family,
            'port_family IS NOT' => null,
        ];

        if ($formFactor) {
            $conditions['form_factor'] = $formFactor;
        }

        return $this->find()
            ->select([
                'id', 'title', 'port_type_name', 'form_factor', 'connector_gender',
                'pin_count', 'max_voltage', 'max_current', 'electrical_shielding',
                'durability_cycles', 'introduced_date', 'deprecated_date',
            ])
            ->where($conditions)
            ->orderBy(['introduced_date' => 'DESC']);
    }

    /**
     * Get electrical specifications for port types
     */
    public function getElectricalSpecs(): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'port_family', 'form_factor',
                'max_voltage', 'max_current', 'data_pin_count',
                'power_pin_count', 'ground_pin_count', 'electrical_shielding',
            ])
            ->where([
                'OR' => [
                    'max_voltage IS NOT' => null,
                    'max_current IS NOT' => null,
                ],
                'port_family IS NOT' => null,
            ])
            ->orderBy(['max_voltage' => 'DESC', 'max_current' => 'DESC']);
    }

    /**
     * Get durability information
     */
    public function getDurabilityInfo(): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'port_family', 'form_factor',
                'durability_cycles', 'electrical_shielding', 'introduced_date',
            ])
            ->where([
                'durability_cycles IS NOT' => null,
                'port_family IS NOT' => null,
            ])
            ->orderBy(['durability_cycles' => 'DESC']);
    }

    /**
     * Get port evolution timeline
     */
    public function getPortEvolution(string $family): Query
    {
        return $this->find()
            ->select([
                'id', 'title', 'form_factor', 'introduced_date',
                'deprecated_date', 'pin_count', 'max_voltage', 'max_current',
            ])
            ->where([
                'port_family' => $family,
                'introduced_date IS NOT' => null,
            ])
            ->orderBy(['introduced_date' => 'ASC']);
    }

    /**
     * Get port statistics
     */
    public function getPortStats(): array
    {
        $cacheKey = 'port_stats';
        $stats = Cache::read($cacheKey);

        if ($stats === null) {
            $stats = [
                'total_ports' => $this->find()
                    ->where(['port_family IS NOT' => null])
                    ->count(),

                'port_families_count' => count($this->getPortFamilies()),

                'highest_voltage' => $this->find()
                    ->where(['max_voltage IS NOT' => null])
                    ->select(['max_voltage'])
                    ->orderBy(['max_voltage' => 'DESC'])
                    ->first()
                    ->max_voltage ?? 0,

                'highest_current' => $this->find()
                    ->where(['max_current IS NOT' => null])
                    ->select(['max_current'])
                    ->orderBy(['max_current' => 'DESC'])
                    ->first()
                    ->max_current ?? 0,

                'average_pin_count' => $this->find()
                    ->where(['pin_count IS NOT' => null])
                    ->select(['avg_pins' => 'AVG(pin_count)'])
                    ->first()
                    ->avg_pins ?? 0,
            ];

            Cache::write($cacheKey, $stats, '30 minutes');
        }

        return $stats;
    }

    /**
     * Search ports by specifications
     */
    public function searchBySpecs(array $filters): Query
    {
        $query = $this->find()
            ->select([
                'id', 'title', 'port_family', 'form_factor', 'connector_gender',
                'pin_count', 'max_voltage', 'max_current', 'durability_cycles',
            ])
            ->where(['port_family IS NOT' => null]);

        if (!empty($filters['min_voltage'])) {
            $query->where(['max_voltage >=' => $filters['min_voltage']]);
        }

        if (!empty($filters['min_current'])) {
            $query->where(['max_current >=' => $filters['min_current']]);
        }

        if (!empty($filters['min_pin_count'])) {
            $query->where(['pin_count >=' => $filters['min_pin_count']]);
        }

        if (!empty($filters['connector_gender'])) {
            $query->where(['connector_gender' => $filters['connector_gender']]);
        }

        return $query->orderBy(['max_voltage' => 'DESC', 'max_current' => 'DESC']);
    }

    /**
     * Clear port-related caches
     */
    public function clearPortCache(): void
    {
        Cache::delete('port_families');
        Cache::delete('port_stats');

        // Clear family-specific caches
        foreach ($this->getPortFamilies() as $family) {
            Cache::delete("form_factors_{$family}");
        }
    }
}
