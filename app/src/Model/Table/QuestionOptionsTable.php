<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * QuestionOptions Model
 *
 * @property \App\Model\Table\QuestionsTable&\Cake\ORM\Association\BelongsTo $Questions
 *
 * @method \App\Model\Entity\QuestionOption newEmptyEntity()
 * @method \App\Model\Entity\QuestionOption newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\QuestionOption> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\QuestionOption get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\QuestionOption findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\QuestionOption patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\QuestionOption> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\QuestionOption|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\QuestionOption saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\QuestionOption>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuestionOption>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuestionOption>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuestionOption> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuestionOption>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuestionOption>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\QuestionOption>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\QuestionOption> deleteManyOrFail(iterable $entities, array $options = [])
 */
class QuestionOptionsTable extends Table
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

        $this->setTable('question_options');
        $this->setDisplayField('option_text');
        $this->setPrimaryKey('id');

        $this->belongsTo('Questions', [
            'foreignKey' => 'question_id',
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
            ->integer('question_id')
            ->notEmptyString('question_id');

        $validator
            ->scalar('option_text')
            ->maxLength('option_text', 255)
            ->requirePresence('option_text', 'create')
            ->notEmptyString('option_text');

        $validator
            ->boolean('is_correct')
            ->notEmptyString('is_correct');

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
        $rules->add($rules->existsIn(['question_id'], 'Questions'), ['errorField' => 'question_id']);

        return $rules;
    }
}
