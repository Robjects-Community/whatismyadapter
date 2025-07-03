<?php
declare(strict_types=1);

namespace App\Model\Table;

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

        return $validator;
    }
}
