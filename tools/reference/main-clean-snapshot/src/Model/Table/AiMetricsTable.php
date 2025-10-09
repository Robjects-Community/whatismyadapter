<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AiMetrics Model
 *
 * @method \App\Model\Entity\AiMetric newEmptyEntity()
 * @method \App\Model\Entity\AiMetric newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AiMetric> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AiMetric get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AiMetric findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AiMetric patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AiMetric> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AiMetric|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AiMetric saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AiMetric>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AiMetric>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AiMetric>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AiMetric> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AiMetric>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AiMetric>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AiMetric>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AiMetric> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AiMetricsTable extends Table
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

        $this->setTable('ai_metrics');
        $this->setDisplayField('task_type');
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
            ->scalar('task_type')
            ->maxLength('task_type', 50)
            ->requirePresence('task_type', 'create')
            ->notEmptyString('task_type');

        $validator
            ->integer('execution_time_ms')
            ->allowEmptyString('execution_time_ms');

        $validator
            ->integer('tokens_used')
            ->allowEmptyString('tokens_used');

        $validator
            ->decimal('cost_usd')
            ->allowEmptyString('cost_usd');

        $validator
            ->boolean('success')
            ->notEmptyString('success');

        $validator
            ->scalar('error_message')
            ->allowEmptyString('error_message');

        $validator
            ->scalar('model_used')
            ->maxLength('model_used', 50)
            ->allowEmptyString('model_used');

        return $validator;
    }

    /**
     * Get total cost by date range
     */
    public function getCostsByDateRange(string $startDate, string $endDate): float
    {
        $result = $this->find()
        ->where(['created >=' => $startDate, 'created <=' => $endDate])
        ->select(['total' => 'SUM(cost_usd)'])
        ->first();

        return (float)($result->total ?? 0);
    }

    /**
     * Get metrics summary by task type
     */
    public function getTaskTypeSummary(string $startDate, string $endDate): array
    {
        return $this->find()
        ->select([
            'task_type',
            'count' => 'COUNT(*)',
            'avg_time' => 'AVG(execution_time_ms)',
            'success_rate' => 'AVG(success) * 100',
            'total_cost' => 'SUM(cost_usd)',
            'total_tokens' => 'SUM(tokens_used)',
        ])
        ->where(['created >=' => $startDate, 'created <=' => $endDate])
        ->groupBy('task_type')
        ->toArray();
    }

    /**
     * Get recent error logs
     */
    public function getRecentErrors(int $limit = 10): array
    {
        return $this->find()
        ->where(['success' => false])
        ->orderBy(['created' => 'DESC'])
        ->limit($limit)
        ->toArray();
    }
}
