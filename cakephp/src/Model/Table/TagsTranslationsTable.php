<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TagsTranslations Model
 *
 * @method \App\Model\Entity\TagsTranslation newEmptyEntity()
 * @method \App\Model\Entity\TagsTranslation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TagsTranslation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TagsTranslation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TagsTranslation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TagsTranslation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TagsTranslation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TagsTranslation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TagsTranslation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TagsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TagsTranslation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TagsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TagsTranslation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TagsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TagsTranslation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TagsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TagsTranslation> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TagsTranslationsTable extends Table
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

        $this->setTable('tags_translations');
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
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('meta_title')
            ->allowEmptyString('meta_title');

        $validator
            ->scalar('meta_description')
            ->allowEmptyString('meta_description');

        $validator
            ->scalar('meta_keywords')
            ->allowEmptyString('meta_keywords');

        $validator
            ->scalar('facebook_description')
            ->allowEmptyString('facebook_description');

        $validator
            ->scalar('linkedin_description')
            ->allowEmptyString('linkedin_description');

        $validator
            ->scalar('instagram_description')
            ->allowEmptyString('instagram_description');

        $validator
            ->scalar('twitter_description')
            ->allowEmptyString('twitter_description');

        return $validator;
    }
}
