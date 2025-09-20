<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ProductsReliabilityLog;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductsReliabilityLogs Model
 *
 * Manages immutable audit logs for reliability score changes. Each log entry
 * contains checksums for integrity verification and complete change history.
 *
 * @method \App\Model\Entity\ProductsReliabilityLog newEmptyEntity()
 * @method \App\Model\Entity\ProductsReliabilityLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliabilityLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductsReliabilityLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsReliabilityLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog|false delete(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsReliabilityLog deleteOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsReliabilityLog> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsReliabilityLogsTable extends Table
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

        $this->setTable('products_reliability_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        // Note: No Timestamp behavior for logs - they should be immutable
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
            ->decimal('from_total_score')
            ->allowEmptyString('from_total_score')
            ->add('from_total_score', 'range', [
                'rule' => ['range', 0.00, 1.00],
                'message' => 'From total score must be between 0.00 and 1.00',
            ]);

        $validator
            ->decimal('to_total_score')
            ->requirePresence('to_total_score', 'create')
            ->notEmptyString('to_total_score')
            ->add('to_total_score', 'range', [
                'rule' => ['range', 0.00, 1.00],
                'message' => 'To total score must be between 0.00 and 1.00',
            ]);

        $validator
            ->allowEmptyString('from_field_scores_json')
            ->add('from_field_scores_json', 'validJson', [
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
                'message' => 'From field scores must be valid JSON',
            ]);

        $validator
            ->requirePresence('to_field_scores_json', 'create')
            ->notEmptyString('to_field_scores_json')
            ->add('to_field_scores_json', 'validJson', [
                'rule' => function ($value) {
                    if (is_string($value)) {
                        json_decode($value);

                        return json_last_error() === JSON_ERROR_NONE;
                    }

                    return false;
                },
                'message' => 'To field scores must be valid JSON',
            ]);

        $validator
            ->scalar('source')
            ->maxLength('source', 20)
            ->requirePresence('source', 'create')
            ->notEmptyString('source')
            ->add('source', 'validSource', [
                'rule' => function ($value) {
                    return in_array($value, self::VALID_SOURCES, true);
                },
                'message' => 'Source must be one of: ' . implode(', ', self::VALID_SOURCES),
            ]);

        $validator
            ->uuid('actor_user_id')
            ->allowEmptyString('actor_user_id');

        $validator
            ->scalar('actor_service')
            ->maxLength('actor_service', 100)
            ->allowEmptyString('actor_service');

        $validator
            ->scalar('message')
            ->allowEmptyString('message');

        $validator
            ->scalar('checksum_sha256')
            ->maxLength('checksum_sha256', 64)
            ->minLength('checksum_sha256', 64)
            ->requirePresence('checksum_sha256', 'create')
            ->notEmptyString('checksum_sha256')
            ->add('checksum_sha256', 'validChecksum', [
                'rule' => function ($value) {
                    return is_string($value) && preg_match('/^[a-f0-9]{64}$/', $value);
                },
                'message' => 'Checksum must be a valid SHA256 hash (64 hex characters)',
            ]);

        $validator
            ->dateTime('created')
            ->requirePresence('created', 'create')
            ->notEmptyDateTime('created');

        return $validator;
    }

    /**
     * Find logs for a specific model and ID
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string $id Entity ID
     * @return \Cake\ORM\Query
     */
    public function findLogsFor(string $model, string $id): Query
    {
        return $this->find()
            ->where(['model' => $model, 'foreign_key' => $id])
            ->orderBy(['created' => 'DESC']);
    }

    /**
     * Get recent logs across all entities for a model
     *
     * @param string $model Model name (e.g., 'Products')
     * @param int $limit Number of logs to return
     * @param int $days Number of days to look back
     * @return \Cake\ORM\Query
     */
    public function findRecentLogs(string $model, int $limit = 50, int $days = 7): Query
    {
        return $this->find()
            ->where([
                'model' => $model,
                'created >=' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
            ])
            ->orderBy(['created' => 'DESC'])
            ->limit($limit);
    }

    /**
     * Find logs by source (user, ai, admin, system)
     *
     * @param string $source Source type
     * @param string|null $model Optionally filter by model
     * @param int $limit Number of results
     * @return \Cake\ORM\Query
     */
    public function findLogsBySource(string $source, ?string $model = null, int $limit = 100): Query
    {
        $query = $this->find()
            ->where(['source' => $source])
            ->orderBy(['created' => 'DESC'])
            ->limit($limit);

        if ($model !== null) {
            $query->where(['model' => $model]);
        }

        return $query;
    }

    /**
     * Find logs by specific user
     *
     * @param string $userId User ID
     * @param string|null $model Optionally filter by model
     * @param int $limit Number of results
     * @return \Cake\ORM\Query
     */
    public function findLogsByUser(string $userId, ?string $model = null, int $limit = 100): Query
    {
        $query = $this->find()
            ->where(['actor_user_id' => $userId])
            ->orderBy(['created' => 'DESC'])
            ->limit($limit);

        if ($model !== null) {
            $query->where(['model' => $model]);
        }

        return $query;
    }

    /**
     * Find significant score changes (large deltas)
     *
     * @param string $model Model name (e.g., 'Products')
     * @param float $minDelta Minimum score change to consider significant
     * @param int $limit Number of results
     * @return \Cake\ORM\Query
     */
    public function findSignificantChanges(string $model, float $minDelta = 0.25, int $limit = 50): Query
    {
        // Use a subquery to calculate the absolute difference
        return $this->find()
            ->where([
                'model' => $model,
                'from_total_score IS NOT' => null,
                'ABS(to_total_score - from_total_score) >=' => $minDelta,
            ])
            ->orderBy(['created' => 'DESC'])
            ->limit($limit);
    }

    /**
     * Verify checksum integrity for logs
     *
     * @param string $model Model name (e.g., 'Products')
     * @param string|null $id Specific entity ID, or null for all
     * @return array ['verified' => int, 'failed' => int, 'failures' => array]
     */
    public function verifyChecksums(string $model, ?string $id = null): array
    {
        $query = $this->find()->where(['model' => $model]);

        if ($id !== null) {
            $query->where(['foreign_key' => $id]);
        }

        $logs = $query->toArray();
        $verified = 0;
        $failed = 0;
        $failures = [];

        foreach ($logs as $log) {
            $computedChecksum = $this->computeLogChecksum($log);

            if ($computedChecksum === $log->checksum_sha256) {
                $verified++;
            } else {
                $failed++;
                $failures[] = [
                    'log_id' => $log->id,
                    'expected' => $log->checksum_sha256,
                    'computed' => $computedChecksum,
                    'created' => $log->created,
                ];
            }
        }

        return [
            'verified' => $verified,
            'failed' => $failed,
            'failures' => $failures,
        ];
    }

    /**
     * Compute checksum for a log record (for verification)
     *
     * @param \App\Model\Entity\ProductsReliabilityLog $log Log entity
     * @return string SHA256 checksum
     */
    public function computeLogChecksum(ProductsReliabilityLog $log): string
    {
        // Reconstruct the payload that was used to generate the original checksum
        $payload = [
            'model' => $log->model,
            'foreign_key' => $log->foreign_key,
            'from_total_score' => $log->from_total_score,
            'to_total_score' => $log->to_total_score,
            'from_field_scores_json' => $log->from_field_scores_json ? json_decode($log->from_field_scores_json, true) : null,
            'to_field_scores_json' => json_decode($log->to_field_scores_json, true),
            'source' => $log->source,
            'actor_user_id' => $log->actor_user_id,
            'actor_service' => $log->actor_service,
            'created' => $log->created->format('c'),
        ];

        // Ensure consistent key ordering
        ksort($payload);

        // Canonicalize JSON representation
        $jsonString = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $jsonString);
    }

    /**
     * Get activity statistics by source
     *
     * @param string $model Model name (e.g., 'Products')
     * @param int $days Number of days to analyze
     * @return array<string, int> Source => count mapping
     */
    public function getActivityBySource(string $model, int $days = 30): array
    {
        $results = $this->find()
            ->where([
                'model' => $model,
                'created >=' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
            ])
            ->select([
                'source',
                'count' => $this->find()->func()->count('*'),
            ])
            ->group('source')
            ->orderBy(['count' => 'DESC'])
            ->toArray();

        $activity = [];
        foreach ($results as $result) {
            $activity[$result->source] = (int)$result->count;
        }

        return $activity;
    }

    /**
     * Get score improvement trends
     *
     * @param string $model Model name (e.g., 'Products')
     * @param int $days Number of days to analyze
     * @return array ['improvements' => int, 'degradations' => int, 'no_change' => int]
     */
    public function getScoreTrends(string $model, int $days = 30): array
    {
        $logs = $this->find()
            ->where([
                'model' => $model,
                'from_total_score IS NOT' => null,
                'created >=' => date('Y-m-d H:i:s', strtotime("-{$days} days")),
            ])
            ->toArray();

        $improvements = 0;
        $degradations = 0;
        $noChange = 0;

        foreach ($logs as $log) {
            $delta = (float)$log->to_total_score - (float)$log->from_total_score;

            if ($delta > 0.01) { // Improved
                $improvements++;
            } elseif ($delta < -0.01) { // Degraded
                $degradations++;
            } else { // No significant change
                $noChange++;
            }
        }

        return [
            'improvements' => $improvements,
            'degradations' => $degradations,
            'no_change' => $noChange,
        ];
    }
}
