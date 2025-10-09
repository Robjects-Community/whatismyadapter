<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Aiprompts Model
 *
 * @method \App\Model\Entity\Aiprompt newEmptyEntity()
 * @method \App\Model\Entity\Aiprompt newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Aiprompt> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Aiprompt get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Aiprompt findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Aiprompt patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Aiprompt> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Aiprompt|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Aiprompt saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Aiprompt>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aiprompt>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aiprompt>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aiprompt> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aiprompt>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aiprompt>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aiprompt>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aiprompt> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AipromptsTable extends Table
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

        $this->setTable('aiprompts');
        $this->setDisplayField('task_type');
        $this->setPrimaryKey('id');
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
            ->scalar('task_type')
            ->maxLength('task_type', 50)
            ->requirePresence('task_type', 'create')
            ->notEmptyString('task_type');

        $validator
            ->scalar('system_prompt')
            ->requirePresence('system_prompt', 'create')
            ->notEmptyString('system_prompt');

        $validator
            ->scalar('model')
            ->maxLength('model', 50)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->integer('max_tokens')
            ->requirePresence('max_tokens', 'create')
            ->notEmptyString('max_tokens');

        $validator
            ->numeric('temperature')
            ->requirePresence('temperature', 'create')
            ->notEmptyString('temperature');

        $validator
            ->dateTime('created')
            ->notEmptyDateTime('created');

        $validator
            ->dateTime('modified')
            ->notEmptyDateTime('modified');

        // New field validations
        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmptyString('status');

        $validator
            ->dateTime('last_used')
            ->allowEmptyDateTime('last_used');

        $validator
            ->nonNegativeInteger('usage_count');

        $validator
            ->numeric('success_rate')
            ->allowEmptyString('success_rate')
            ->add('success_rate', 'range', [
                'rule' => function ($value) {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return $value >= 0 && $value <= 100;
                },
                'message' => 'Success rate must be between 0 and 100.',
            ]);

        $validator
            ->allowEmptyString('description');

        $validator
            ->allowEmptyString('preview_sample');

        $validator
            ->allowEmptyString('expected_output');

        $validator
            ->boolean('is_active');

        $validator
            ->scalar('category')
            ->maxLength('category', 100)
            ->allowEmptyString('category');

        $validator
            ->scalar('version')
            ->maxLength('version', 50)
            ->regex('version', '/^[0-9A-Za-z.\-_]+$/', 'Only letters, numbers, dot, dash and underscore are allowed.')
            ->allowEmptyString('version');

        return $validator;
    }

    /**
     * Custom finder to return results indexed by id
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query instance
     * @param array<string, mixed> $options Options array
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findIndexedById(SelectQuery $query, array $options): SelectQuery
    {
        return $query->formatResults(function ($results) {
            return $results->indexBy('id');
        });
    }
}
