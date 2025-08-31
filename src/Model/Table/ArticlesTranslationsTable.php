<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArticlesTranslations Model
 *
 * @method \App\Model\Entity\ArticlesTranslation newEmptyEntity()
 * @method \App\Model\Entity\ArticlesTranslation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArticlesTranslation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArticlesTranslation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArticlesTranslation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArticlesTranslation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArticlesTranslation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArticlesTranslation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArticlesTranslation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArticlesTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArticlesTranslation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArticlesTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArticlesTranslation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArticlesTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArticlesTranslation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArticlesTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArticlesTranslation> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArticlesTranslationsTable extends Table
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

        $this->setTable('articles_translations');
        $this->setDisplayField('title');
        $this->setPrimaryKey(['id', 'locale']);
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->allowEmptyString('title');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->scalar('summary')
            ->allowEmptyString('summary');

        return $validator;
    }
}
