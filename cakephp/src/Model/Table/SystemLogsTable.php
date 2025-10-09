<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SystemLogs Model
 *
 * @method \App\Model\Entity\SystemLog newEmptyEntity()
 * @method \App\Model\Entity\SystemLog newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SystemLog> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SystemLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SystemLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SystemLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SystemLog> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SystemLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SystemLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SystemLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SystemLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SystemLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SystemLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SystemLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SystemLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SystemLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SystemLog> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SystemLogsTable extends Table
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

        $this->setTable('system_logs');
        $this->setDisplayField('level');
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
            ->scalar('level')
            ->maxLength('level', 50)
            ->requirePresence('level', 'create')
            ->notEmptyString('level');

        $validator
            ->scalar('message')
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        $validator
            ->scalar('context')
            ->allowEmptyString('context');

        $validator
            ->scalar('group_name')
            ->maxLength('group_name', 100)
            ->requirePresence('group_name', 'create')
            ->notEmptyString('group_name');

        return $validator;
    }
}
