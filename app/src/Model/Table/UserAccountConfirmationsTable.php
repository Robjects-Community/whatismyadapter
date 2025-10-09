<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserAccountConfirmations Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\UserAccountConfirmation newEmptyEntity()
 * @method \App\Model\Entity\UserAccountConfirmation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserAccountConfirmation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserAccountConfirmation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserAccountConfirmation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserAccountConfirmation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserAccountConfirmation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserAccountConfirmation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserAccountConfirmation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserAccountConfirmation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserAccountConfirmation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserAccountConfirmation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserAccountConfirmation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserAccountConfirmation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserAccountConfirmation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserAccountConfirmation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserAccountConfirmation> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserAccountConfirmationsTable extends Table
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

        $this->setTable('user_account_confirmations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->scalar('user_id')
            ->maxLength('user_id', 36)
            ->notEmptyString('user_id');

        $validator
            ->scalar('confirmation_code')
            ->maxLength('confirmation_code', 36)
            ->requirePresence('confirmation_code', 'create')
            ->notEmptyString('confirmation_code')
            ->add('confirmation_code', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['confirmation_code']), ['errorField' => 'confirmation_code']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
