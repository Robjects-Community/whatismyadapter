<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductsTags Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsTo $Tags
 * @method \App\Model\Entity\ProductsTag newEmptyEntity()
 * @method \App\Model\Entity\ProductsTag newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsTag> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductsTag get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductsTag findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductsTag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsTag> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductsTag|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsTag saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTag>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTag> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTag>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTag> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsTagsTable extends Table
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

        $this->setTable('products_tags');

        $this->setPrimaryKey(['product_id', 'tag_id']);

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
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
            ->notEmptyString('product_id');

        $validator
            ->uuid('tag_id')
            ->notEmptyString('tag_id');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'), ['errorField' => 'product_id']);
        $rules->add($rules->existsIn(['tag_id'], 'Tags'), ['errorField' => 'tag_id']);

        return $rules;
    }
}
