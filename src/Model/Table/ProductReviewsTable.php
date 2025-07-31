<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductReviews Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ProductReview newEmptyEntity()
 * @method \App\Model\Entity\ProductReview newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductReview> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductReview get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductReview findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductReview patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductReview> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductReview|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductReview saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductReviewsTable extends Table
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

        $this->setTable('product_reviews');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->uuid('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->integer('rating')
            ->range('rating', [1, 5])
            ->requirePresence('rating', 'create')
            ->notEmptyString('rating');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->allowEmptyString('title');

        $validator
            ->scalar('review_text')
            ->allowEmptyString('review_text');

        $validator
            ->boolean('verified_purchase')
            ->notEmptyString('verified_purchase');

        $validator
            ->boolean('is_approved')
            ->notEmptyString('is_approved');

        $validator
            ->boolean('is_featured')
            ->notEmptyString('is_featured');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
