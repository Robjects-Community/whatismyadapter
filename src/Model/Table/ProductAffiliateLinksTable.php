<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductAffiliateLinks Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\ProductAffiliateLink newEmptyEntity()
 * @method \App\Model\Entity\ProductAffiliateLink newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductAffiliateLink> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductAffiliateLink get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductAffiliateLink findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductAffiliateLink patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductAffiliateLink> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductAffiliateLink|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductAffiliateLink saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductAffiliateLinksTable extends Table
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

        $this->setTable('product_affiliate_links');
        $this->setDisplayField('merchant_name');
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
            ->scalar('affiliate_network')
            ->maxLength('affiliate_network', 100)
            ->requirePresence('affiliate_network', 'create')
            ->notEmptyString('affiliate_network');

        $validator
            ->scalar('merchant_name')
            ->maxLength('merchant_name', 255)
            ->requirePresence('merchant_name', 'create')
            ->notEmptyString('merchant_name');

        $validator
            ->scalar('affiliate_url')
            ->requirePresence('affiliate_url', 'create')
            ->notEmptyString('affiliate_url');

        $validator
            ->boolean('is_primary')
            ->notEmptyString('is_primary');

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
        $rules->add($rules->existsIn('product_id', 'Products'), ['errorField' => 'product_id']);

        return $rules;
    }
}
