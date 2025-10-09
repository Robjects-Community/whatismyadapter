<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ProductsReliability;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductsReliability Model
 *
 * Manages polymorphic reliability summaries for any model. Stores current
 * reliability scores, completeness percentages, and audit information.
 *
 * @method \App\Model\Entity\ProductsReliability newEmptyEntity()
 * @method \App\Model\Entity\ProductsReliability newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliability> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliability get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductsReliability findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductsReliability patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliability> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliability|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliability saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliability>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliability> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ProductsReliability|false delete(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliability deleteOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliability>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliability> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsReliabilityTable extends Table
{
    /**
     * Valid sources for reliability updates
     */
    public const VALID_SOURCES = ['user', 'ai', 'admin', 'system'];

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products_reliability');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('model')
            ->maxLength('model', 20)
            ->requirePresence('model', 'create')
            ->notEmptyString('model')
            ->add('model', 'validModel', [
                'rule' => function ($value) {
                    return is_string($value) && preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $value);
                },
                'message' => 'Model name must be a valid class name',
            ]);

        $validator
            ->uuid('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmptyString('foreign_key');

        $validator
            ->decimal('total_score')
            ->requirePresence('total_score', 'create')
            ->notEmptyString('total_score')
            ->add('total_score', 'range', [
                'rule' => ['range', 0.00, 1.00],
                'message' => 'Total score must be between 0.00 and 1.00',
            ]);

        $validator
            ->decimal('completeness_percent')
            ->requirePresence('completeness_percent', 'create')
            ->notEmptyString('completeness_percent')
            ->add('completeness_percent', 'range', [
                'rule' => ['range', 0.00, 100.00],
                'message' => 'Completeness percent must be between 0.00 and 100.00',
            ]);

        $validator
            ->allowEmptyString('field_scores_json')
            ->add('field_scores_json', 'validJson', [
                'rule' => function ($value) {
                    if ($value === null) {
                        return true;
                    }
                    if (is_string($value)) {
                        json_decode($value);

                        return json_last_error() === JSON_ERROR_NONE;
                    }

                    return false;
                },
                'message' => 'Field scores must be valid JSON',
            ]);

        $validator
            ->scalar('scoring_version')
            ->maxLength('scoring_version', 32)
            ->requirePresence('scoring_version', 'create')
            ->notEmptyString('scoring_version');

        $validator
            ->scalar('last_source')
            ->maxLength('last_source', 20)
            ->requirePresence('last_source', 'create')
            ->notEmptyString('last_source')
            ->add('last_source', 'validSource', [
                'rule' => function ($value) {
                    return in_array($value, self::VALID_SOURCES, true);
                },
                'message' => 'Last source must be one of: ' . implode(', ', self::VALID_SOURCES),
            ]);

        $validator
            ->dateTime('last_calculated')
            ->allowEmptyDateTime('last_calculated');

        $validator
            ->uuid('updated_by_user_id')
            ->allowEmptyString('updated_by_user_id');

        $validator
            ->scalar('updated_by_service')
            ->maxLength('updated_by_service', 100)
            ->allowEmptyString('updated_by_service');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // Ensure unique combination of model and foreign_key
        $rules->add($rules->isUnique(['model', 'foreign_key']), [
            'errorField' => 'foreign_key',
            'message' => 'A reliability record already exists for this model and ID',
        ]);

        return $rules;
    }

    /**
     * Find reliability summary for a specific model and ID
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $id Entity ID
     * @return \App\Model\Entity\ProductsReliability|null
     */
    public function findSummaryFor(string $model, string $id): ?ProductsReliability
    {
        return $this->find()
            ->where(['model' => $model, 'foreign_key' => $id])
            ->first();
    }

    /**
     * Get reliability summaries for multiple entities of the same model
     *
     * @param string $model Model name (e.g., 'Products')
     * @param array<string> $ids Array of entity IDs
     * @return array<string, \App\Model\Entity\ProductsReliability> Keyed by foreign_key
     */
    public function findSummariesFor(string $model, array $ids): array
    {
        $results = $this->find()
            ->where(['model' => $model, 'foreign_key IN' => $ids])
            ->toArray();

        $summaries = [];
        foreach ($results as $result) {
            $summaries[$result->foreign_key] = $result;
        }

        return $summaries;
    }

    /**
     * Get top scoring entities for a model
     *
     * @param string $model Model name (e.g., 'Products')
     * @param int $limit Number of results to return
     * @param float $minScore Minimum score threshold
     * @return \Cake\ORM\Query
     */
    public function findTopScoring(string $model, int $limit = 10, float $minScore = 0.50): Query
    {
        return $this->find()
            ->where([
                'model' => $model,
                'total_score >=' => $minScore,
            ])
            ->orderBy(['total_score' => 'DESC', 'completeness_percent' => 'DESC'])
            ->limit($limit);
    }

    /**
     * Get entities that need attention (low scores or incomplete)
     *
     * @param string $model Model name (e.g., 'Products')
     * @param float $maxScore Maximum score threshold
     * @param float $maxCompleteness Maximum completeness threshold
     * @return \Cake\ORM\Query
     */
    public function findNeedingAttention(string $model, float $maxScore = 0.70, float $maxCompleteness = 80.0): Query
    {
        return $this->find()
            ->where([
                'model' => $model,
                'OR' => [
                    'total_score <=' => $maxScore,
                    'completeness_percent <=' => $maxCompleteness,
                ],
            ])
            ->orderBy(['total_score' => 'ASC', 'completeness_percent' => 'ASC']);
    }

    /**
     * Get reliability statistics for a model
     *
     * @param string $model Model name (e.g., 'Products')
     * @return array Statistics summary
     */
    public function getStatsFor(string $model): array
    {
        $query = $this->find()
            ->where(['model' => $model])
            ->select([
                'count' => $this->find()->func()->count('*'),
                'avg_score' => $this->find()->func()->avg('total_score'),
                'min_score' => $this->find()->func()->min('total_score'),
                'max_score' => $this->find()->func()->max('total_score'),
                'avg_completeness' => $this->find()->func()->avg('completeness_percent'),
            ])
            ->first();

        if (!$query) {
            return [
                'count' => 0,
                'avg_score' => 0.00,
                'min_score' => 0.00,
                'max_score' => 0.00,
                'avg_completeness' => 0.00,
            ];
        }

        return [
            'count' => (int)$query->count,
            'avg_score' => round((float)$query->avg_score, 2),
            'min_score' => round((float)$query->min_score, 2),
            'max_score' => round((float)$query->max_score, 2),
            'avg_completeness' => round((float)$query->avg_completeness, 2),
        ];
    }

    /**
     * Get scoring version distribution
     *
     * @param string|null $model Optionally filter by model
     * @return array<string, int> Version => count mapping
     */
    public function getScoringVersions(?string $model = null): array
    {
        $query = $this->find()
            ->select([
                'scoring_version',
                'count' => $this->find()->func()->count('*'),
            ])
            ->group('scoring_version');

        if ($model) {
            $query->where(['model' => $model]);
        }

        $results = $query->toArray();
        $versions = [];

        foreach ($results as $result) {
            $versions[$result->scoring_version] = (int)$result->count;
        }

        return $versions;
    }

    /**
     * Get records updated by source
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string|null $source Filter by source (user, ai, admin, system)
     * @param int $days Number of days to look back
     * @return \Cake\ORM\Query
     */
    public function findRecentUpdates(string $model, ?string $source = null, int $days = 7): Query
    {
        $query = $this->find()
            ->where([
                'model' => $model,
                'modified >=' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
            ])
            ->orderBy(['modified' => 'DESC']);

        if ($source && in_array($source, self::VALID_SOURCES, true)) {
            $query->where(['last_source' => $source]);
        }

        return $query;
    }
}
