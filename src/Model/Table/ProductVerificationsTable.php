<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductVerifications Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ProductVerification newEmptyEntity()
 * @method \App\Model\Entity\ProductVerification newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductVerification> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductVerification get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductVerification findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductVerification patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductVerification> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductVerification|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductVerification saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ProductVerificationsTable extends Table
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

        $this->setTable('product_verifications');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'verifier_user_id',
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
            ->uuid('verifier_user_id')
            ->allowEmptyString('verifier_user_id');

        $validator
            ->scalar('verification_type')
            ->requirePresence('verification_type', 'create')
            ->notEmptyString('verification_type');

        $validator
            ->decimal('verification_score')
            ->range('verification_score', [0, 5])
            ->requirePresence('verification_score', 'create')
            ->notEmptyString('verification_score');

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
        $rules->add($rules->existsIn('verifier_user_id', 'Users'), ['errorField' => 'verifier_user_id']);

        return $rules;
    }
}
