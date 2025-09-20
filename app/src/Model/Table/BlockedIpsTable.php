<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BlockedIps Model
 *
 * @method \App\Model\Entity\BlockedIp newEmptyEntity()
 * @method \App\Model\Entity\BlockedIp newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\BlockedIp> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BlockedIp get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\BlockedIp findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\BlockedIp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\BlockedIp> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BlockedIp|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\BlockedIp saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\BlockedIp>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BlockedIp>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BlockedIp>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BlockedIp> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BlockedIp>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BlockedIp>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\BlockedIp>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\BlockedIp> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BlockedIpsTable extends Table
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

        $this->setTable('blocked_ips');
        $this->setDisplayField('ip_address');
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
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address')
            ->add('ip_address', 'unique', ['rule' => 'validateUnique', 'provider' => 'table'])
            ->add('ip_address', 'validIP', ['rule' => 'ip']);

        $validator
            ->scalar('reason')
            ->allowEmptyString('reason');

        $validator
            ->dateTime('blocked_at')
            ->notEmptyDateTime('blocked_at');

        $validator
            ->dateTime('expires_at')
            ->allowEmptyDateTime('expires_at');

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
        $rules->add($rules->isUnique(['ip_address']), ['errorField' => 'ip_address']);

        return $rules;
    }
}
