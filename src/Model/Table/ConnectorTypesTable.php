<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ConnectorTypes Model
 *
 * @property \App\Model\Table\ProductConnectorsTable&\Cake\ORM\Association\HasMany $ProductConnectors
 *
 * @method \App\Model\Entity\ConnectorType newEmptyEntity()
 * @method \App\Model\Entity\ConnectorType newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ConnectorType> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ConnectorType get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ConnectorType findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ConnectorType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ConnectorType> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ConnectorType|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ConnectorType saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ConnectorTypesTable extends Table
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

        $this->setTable('connector_types');
        $this->setDisplayField('display_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('ProductConnectors', [
            'foreignKey' => 'connector_type_id',
        ]);
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
            ->scalar('slug')
            ->maxLength('slug', 100)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('display_name')
            ->maxLength('display_name', 255)
            ->requirePresence('display_name', 'create')
            ->notEmptyString('display_name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
