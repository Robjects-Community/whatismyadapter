<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Adapters Model
 *
 * @method \App\Model\Entity\Adapter newEmptyEntity()
 * @method \App\Model\Entity\Adapter newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Adapter> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Adapter get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Adapter findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Adapter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Adapter> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Adapter|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Adapter saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Adapter>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Adapter>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Adapter>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Adapter> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Adapter>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Adapter>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Adapter>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Adapter> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AdaptersTable extends Table
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

        $this->setTable('adapters');
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
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('gender')
            ->requirePresence('gender', 'create')
            ->notEmptyString('gender');

        $validator
            ->allowEmptyString('additional_params');

        return $validator;
    }
}
