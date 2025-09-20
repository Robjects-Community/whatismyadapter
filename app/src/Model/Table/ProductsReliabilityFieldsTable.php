<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductsReliabilityFields Model
 *
 * Manages individual field reliability scores for any model. Stores per-field
 * scores, weights, and scoring rationale for detailed analysis.
 *
 * @method \App\Model\Entity\ProductsReliabilityField newEmptyEntity()
 * @method \App\Model\Entity\ProductsReliabilityField newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliabilityField> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductsReliabilityField findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliabilityField> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityField>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityField> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField|false delete(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityField deleteOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityField>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityField> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsReliabilityFieldsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products_reliability_fields');
        $this->setDisplayField(['model', 'foreign_key', 'field']);
        $this->setPrimaryKey(['model', 'foreign_key', 'field']);

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
            ->scalar('field')
            ->maxLength('field', 64)
            ->requirePresence('field', 'create')
            ->notEmptyString('field')
            ->add('field', 'validField', [
                'rule' => function ($value) {
                    return is_string($value) && preg_match('/^[a-z][a-z0-9_]*$/', $value);
                },
                'message' => 'Field name must be a valid database field name',
            ]);

        $validator
            ->decimal('score')
            ->requirePresence('score', 'create')
            ->notEmptyString('score')
            ->add('score', 'range', [
                'rule' => ['range', 0.00, 1.00],
                'message' => 'Score must be between 0.00 and 1.00',
            ]);

        $validator
            ->decimal('weight')
            ->requirePresence('weight', 'create')
            ->notEmptyString('weight')
            ->add('weight', 'range', [
                'rule' => ['range', 0.000, 1.000],
                'message' => 'Weight must be between 0.000 and 1.000',
            ]);

        $validator
            ->decimal('max_score')
            ->requirePresence('max_score', 'create')
            ->notEmptyString('max_score')
            ->add('max_score', 'range', [
                'rule' => ['range', 0.00, 1.00],
                'message' => 'Max score must be between 0.00 and 1.00',
            ]);

        $validator
            ->scalar('notes')
            ->maxLength('notes', 255)
            ->allowEmptyString('notes');

        return $validator;
    }

    /**
     * Find field scores for a specific model and ID
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $id Entity ID
     * @return array<string, \App\Model\Entity\ProductsReliabilityField> Keyed by field name
     */
    public function findFieldsFor(string $model, string $id): array
    {
        $results = $this->find()
            ->where(['model' => $model, 'foreign_key' => $id])
            ->orderBy(['field' => 'ASC'])
            ->toArray();

        $fields = [];
        foreach ($results as $result) {
            $fields[$result->field] = $result;
        }

        return $fields;
    }

    /**
     * Get field scores summary for multiple entities
     *
     * @param string $model Model name (e.g., 'Products')
     * @param array<string> $ids Array of entity IDs
     * @return array<string, array<string, \App\Model\Entity\ProductsReliabilityField>> Keyed by foreign_key then field
     */
    public function findFieldsForMultiple(string $model, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $results = $this->find()
            ->where(['model' => $model, 'foreign_key IN' => $ids])
            ->orderBy(['foreign_key' => 'ASC', 'field' => 'ASC'])
            ->toArray();

        $grouped = [];
        foreach ($results as $result) {
            $grouped[$result->foreign_key][$result->field] = $result;
        }

        return $grouped;
    }

    /**
     * Get field performance statistics across all entities of a model
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string|null $field Specific field name, or null for all fields
     * @return array<string, array> Field name => statistics
     */
    public function getFieldStats(string $model, ?string $field = null): array
    {
        $query = $this->find()
            ->where(['model' => $model])
            ->select([
                'field',
                'count' => $this->find()->func()->count('*'),
                'avg_score' => $this->find()->func()->avg('score'),
                'min_score' => $this->find()->func()->min('score'),
                'max_score' => $this->find()->func()->max('score'),
                'avg_weight' => $this->find()->func()->avg('weight'),
            ])
            ->group('field');

        if ($field !== null) {
            $query->where(['field' => $field]);
        }

        $results = $query->toArray();
        $stats = [];

        foreach ($results as $result) {
            $stats[$result->field] = [
                'count' => (int)$result->count,
                'avg_score' => round((float)$result->avg_score, 3),
                'min_score' => round((float)$result->min_score, 2),
                'max_score' => round((float)$result->max_score, 2),
                'avg_weight' => round((float)$result->avg_weight, 3),
            ];
        }

        return $stats;
    }

    /**
     * Find entities with low scores for a specific field
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $field Field name
     * @param float $maxScore Maximum score threshold
     * @return \Cake\ORM\Query
     */
    public function findLowScoringField(string $model, string $field, float $maxScore = 0.50): Query
    {
        return $this->find()
            ->where([
                'model' => $model,
                'field' => $field,
                'score <=' => $maxScore,
            ])
            ->orderBy(['score' => 'ASC']);
    }

    /**
     * Get entities missing specific fields (score = 0)
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $field Field name
     * @return \Cake\ORM\Query
     */
    public function findMissingField(string $model, string $field): Query
    {
        return $this->find()
            ->where([
                'model' => $model,
                'field' => $field,
                'score' => 0.00,
            ])
            ->orderBy(['modified' => 'ASC']);
    }

    /**
     * Get field weight distribution for a model
     *
     * @param string $model Model name (e.g., 'Products')
     * @return array<string, float> Field => average weight
     */
    public function getFieldWeights(string $model): array
    {
        $results = $this->find()
            ->where(['model' => $model])
            ->select([
                'field',
                'avg_weight' => $this->find()->func()->avg('weight'),
            ])
            ->group('field')
            ->orderBy(['avg_weight' => 'DESC'])
            ->toArray();

        $weights = [];
        foreach ($results as $result) {
            $weights[$result->field] = round((float)$result->avg_weight, 3);
        }

        return $weights;
    }

    /**
     * Get top performing fields (highest average scores)
     *
     * @param string $model Model name (e.g., 'Products')
     * @param int $limit Number of results
     * @return array<string, float> Field => average score
     */
    public function getTopPerformingFields(string $model, int $limit = 10): array
    {
        $results = $this->find()
            ->where(['model' => $model])
            ->select([
                'field',
                'avg_score' => $this->find()->func()->avg('score'),
            ])
            ->group('field')
            ->orderBy(['avg_score' => 'DESC'])
            ->limit($limit)
            ->toArray();

        $scores = [];
        foreach ($results as $result) {
            $scores[$result->field] = round((float)$result->avg_score, 3);
        }

        return $scores;
    }
}
