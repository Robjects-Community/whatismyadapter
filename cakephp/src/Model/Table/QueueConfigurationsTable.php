<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * QueueConfigurations Model
 *
 * @method \App\Model\Entity\QueueConfiguration newEmptyEntity()
 * @method \App\Model\Entity\QueueConfiguration newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\QueueConfiguration> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\QueueConfiguration get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\QueueConfiguration findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\QueueConfiguration patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\QueueConfiguration> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\QueueConfiguration|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\QueueConfiguration saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\QueueConfiguration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QueueConfiguration>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QueueConfiguration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QueueConfiguration> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QueueConfiguration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QueueConfiguration>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QueueConfiguration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QueueConfiguration> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QueueConfigurationsTable extends Table
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

        $this->setTable('queue_configurations');
        $this->setDisplayField('name');
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
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('config_key')
            ->maxLength('config_key', 50)
            ->requirePresence('config_key', 'create')
            ->notEmptyString('config_key')
            ->add('config_key', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('queue_type')
            ->maxLength('queue_type', 20)
            ->notEmptyString('queue_type');

        $validator
            ->scalar('queue_name')
            ->maxLength('queue_name', 100)
            ->requirePresence('queue_name', 'create')
            ->notEmptyString('queue_name');

        $validator
            ->scalar('host')
            ->maxLength('host', 255)
            ->notEmptyString('host');

        $validator
            ->integer('port')
            ->allowEmptyString('port');

        $validator
            ->scalar('username')
            ->maxLength('username', 100)
            ->allowEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        $validator
            ->integer('db_index')
            ->allowEmptyString('db_index');

        $validator
            ->scalar('vhost')
            ->maxLength('vhost', 100)
            ->allowEmptyString('vhost');

        $validator
            ->scalar('exchange')
            ->maxLength('exchange', 100)
            ->allowEmptyString('exchange');

        $validator
            ->scalar('routing_key')
            ->maxLength('routing_key', 100)
            ->allowEmptyString('routing_key');

        $validator
            ->boolean('ssl_enabled')
            ->notEmptyString('ssl_enabled');

        $validator
            ->boolean('persistent')
            ->notEmptyString('persistent');

        $validator
            ->integer('max_workers')
            ->notEmptyString('max_workers');

        $validator
            ->integer('priority')
            ->notEmptyString('priority');

        $validator
            ->boolean('enabled')
            ->notEmptyString('enabled');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->allowEmptyString('config_data');

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
        $rules->add($rules->isUnique(['config_key']), ['errorField' => 'config_key']);

        return $rules;
    }
}
