<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductSpecifications Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\ProductSpecification newEmptyEntity()
 * @method \App\Model\Entity\ProductSpecification newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductSpecification> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductSpecification get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductSpecification findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductSpecification patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductSpecification> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductSpecification|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductSpecification saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductSpecificationsTable extends Table
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

        $this->setTable('product_specifications');
        $this->setDisplayField('spec_name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
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
            ->scalar('spec_category')
            ->maxLength('spec_category', 100)
            ->requirePresence('spec_category', 'create')
            ->notEmptyString('spec_category');

        $validator
            ->scalar('spec_name')
            ->maxLength('spec_name', 255)
            ->requirePresence('spec_name', 'create')
            ->notEmptyString('spec_name');

        $validator
            ->scalar('spec_value')
            ->requirePresence('spec_value', 'create')
            ->notEmptyString('spec_value');

        $validator
            ->boolean('is_filterable')
            ->notEmptyString('is_filterable');

        $validator
            ->boolean('is_searchable')
            ->notEmptyString('is_searchable');

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

        return $rules;
    }
}
