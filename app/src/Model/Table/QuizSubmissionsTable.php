<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * QuizSubmissions Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\QuizSubmission newEmptyEntity()
 * @method \App\Model\Entity\QuizSubmission newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\QuizSubmission> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\QuizSubmission get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\QuizSubmission findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\QuizSubmission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\QuizSubmission> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\QuizSubmission|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\QuizSubmission saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\QuizSubmission>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuizSubmission>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuizSubmission>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuizSubmission> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuizSubmission>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuizSubmission>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuizSubmission>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuizSubmission> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class QuizSubmissionsTable extends Table
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

        $this->setTable('quiz_submissions');
        $this->setDisplayField('session_id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        // Note: UUID generation should be handled at the entity level or via database defaults

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->uuid('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('session_id')
            ->maxLength('session_id', 64)
            ->requirePresence('session_id', 'create')
            ->notEmptyString('session_id');

        $validator
            ->scalar('quiz_type')
            ->maxLength('quiz_type', 20)
            ->inList('quiz_type', ['akinator', 'comprehensive'])
            ->notEmptyString('quiz_type');

        $validator
            ->requirePresence('answers', 'create')
            ->notEmptyString('answers')
            ->add('answers', 'json', [
                'rule' => function ($value, $context) {
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);

                        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
                    }

                    return is_array($value);
                },
                'message' => 'Answers must be valid JSON array',
            ]);

        $validator
            ->allowEmptyString('matched_product_ids');

        $validator
            ->allowEmptyString('confidence_scores');

        $validator
            ->scalar('result_summary')
            ->allowEmptyString('result_summary');

        $validator
            ->allowEmptyString('analytics');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
