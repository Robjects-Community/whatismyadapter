<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductConnectors Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 * @property \App\Model\Table\ConnectorTypesTable&\Cake\ORM\Association\BelongsTo $ConnectorTypes
 *
 * @method \App\Model\Entity\ProductConnector newEmptyEntity()
 * @method \App\Model\Entity\ProductConnector newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductConnector> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductConnector get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductConnector findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductConnector patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductConnector> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductConnector|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductConnector saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductConnectorsTable extends Table
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

        $this->setTable('product_connectors');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('ConnectorTypes', [
            'foreignKey' => 'connector_type_id',
            'joinType' => 'INNER',
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
            ->uuid('product_id')
            ->requirePresence('product_id', 'create')
            ->notEmptyString('product_id');

        $validator
            ->uuid('connector_type_id')
            ->requirePresence('connector_type_id', 'create')
            ->notEmptyString('connector_type_id');

        $validator
            ->scalar('connector_role')
            ->requirePresence('connector_role', 'create')
            ->notEmptyString('connector_role');

        $validator
            ->integer('quantity')
            ->notEmptyString('quantity');

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
        $rules->add($rules->existsIn('product_id', 'Products'), ['errorField' => 'product_id']);
        $rules->add($rules->existsIn('connector_type_id', 'ConnectorTypes'), ['errorField' => 'connector_type_id']);

        return $rules;
    }
}
