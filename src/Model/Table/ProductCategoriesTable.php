<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductCategories Model
 *
 * @property \App\Model\Table\ProductCategoriesTable&\Cake\ORM\Association\BelongsTo $ParentProductCategories
 * @property \App\Model\Table\ProductCategoriesTable&\Cake\ORM\Association\HasMany $ChildProductCategories
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsToMany $Products
 *
 * @method \App\Model\Entity\ProductCategory newEmptyEntity()
 * @method \App\Model\Entity\ProductCategory newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductCategory> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductCategory get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductCategory findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductCategory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductCategory> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductCategory|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductCategory saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductCategoriesTable extends Table
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

        $this->setTable('product_categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('ParentProductCategories', [
            'className' => 'ProductCategories',
            'foreignKey' => 'parent_id',
        ]);

        $this->hasMany('ChildProductCategories', [
            'className' => 'ProductCategories',
            'foreignKey' => 'parent_id',
        ]);

        $this->belongsToMany('Products', [
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'product_id',
            'joinTable' => 'products_categories',
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
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->uuid('parent_id')
            ->allowEmptyString('parent_id');

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
        $rules->add($rules->existsIn('parent_id', 'ParentProductCategories'), ['errorField' => 'parent_id']);
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }
}
