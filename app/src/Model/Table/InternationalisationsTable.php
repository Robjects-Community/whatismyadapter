<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Internationalisations Model
 *
 * @method \App\Model\Entity\Internationalisation newEmptyEntity()
 * @method \App\Model\Entity\Internationalisation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Internationalisation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Internationalisation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Internationalisation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Internationalisation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Internationalisation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Internationalisation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Internationalisation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Internationalisation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Internationalisation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Internationalisation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Internationalisation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Internationalisation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Internationalisation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Internationalisation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Internationalisation> deleteManyOrFail(iterable $entities, array $options = [])
 */
class InternationalisationsTable extends Table
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

        $this->setTable('internationalisations');
        $this->setDisplayField('message_id');
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
            ->scalar('locale')
            ->maxLength('locale', 10)
            ->requirePresence('locale', 'create')
            ->notEmptyString('locale');

        $validator
            ->scalar('message_id')
            ->requirePresence('message_id', 'create')
            ->notEmptyString('message_id')
            ->add('message_id', 'unique', [
                'rule' => function ($value, $context) {
                    $count = $this->find()
                        ->where(['message_id' => $value, 'locale' => $context['data']['locale']])
                        ->count();

                    return $count === 0;
                },
                'message' => __('This message_id must be unique for the given locale.'),
            ]);

        $validator
            ->scalar('message_str')
            ->allowEmptyString('message_str');

        $validator
            ->dateTime('created')
            ->notEmptyDateTime('created');

        $validator
            ->dateTime('modified')
            ->notEmptyDateTime('modified');

        return $validator;
    }
}
