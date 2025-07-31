<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductsTranslations Model
 *
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\ProductsTranslation newEmptyEntity()
 * @method \App\Model\Entity\ProductsTranslation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsTranslation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductsTranslation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductsTranslation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductsTranslation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductsTranslation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductsTranslation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductsTranslation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTranslation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTranslation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTranslation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductsTranslation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductsTranslation> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsTranslationsTable extends Table
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

        $this->setTable('products_translations');
        $this->setDisplayField(['title', 'locale']);
        $this->setPrimaryKey(['id', 'locale']);

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'id',
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
            ->uuid('id')
            ->requirePresence('id', 'create')
            ->notEmptyString('id');

        $validator
            ->scalar('locale')
            ->maxLength('locale', 5)
            ->requirePresence('locale', 'create')
            ->notEmptyString('locale')
            ->add('locale', 'validLocale', [
                'rule' => function ($value) {
                    // Validate locale format (e.g., en_GB, fr_FR)
                    return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $value);
                },
                'message' => 'Locale must be in format: xx_XX (e.g., en_GB, fr_FR)'
            ]);

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->allowEmptyString('title');

        $validator
            ->scalar('lede')
            ->maxLength('lede', 400)
            ->allowEmptyString('lede');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator  
            ->scalar('summary')
            ->allowEmptyString('summary');

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

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['id'], 'Products'), ['errorField' => 'id']);

        // Ensure unique combination of product and locale
        $rules->add($rules->isUnique(['id', 'locale']), [
            'errorField' => 'locale',
            'message' => 'A translation for this product already exists in this locale.'
        ]);

        return $rules;
    }

    /**
     * Find translations by locale
     *
     * @param \Cake\ORM\Query $query
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function findByLocale($query, $options)
    {
        $locale = $options['locale'] ?? 'en_GB';
        
        return $query->where(['ProductsTranslations.locale' => $locale]);
    }

    /**
     * Find complete translations (having both title and body)
     *
     * @param \Cake\ORM\Query $query
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function findComplete($query, $options)
    {
        return $query->where([
            'ProductsTranslations.title IS NOT' => null,
            'ProductsTranslations.title !=' => '',
            'ProductsTranslations.body IS NOT' => null,
            'ProductsTranslations.body !=' => '',
        ]);
    }

    /**
     * Find translations with SEO metadata
     *
     * @param \Cake\ORM\Query $query
     * @param array $options
     * @return \Cake\ORM\Query
     */
    public function findWithSeo($query, $options)
    {
        return $query->where([
            'ProductsTranslations.meta_title IS NOT' => null,
            'ProductsTranslations.meta_title !=' => '',
            'ProductsTranslations.meta_description IS NOT' => null,
            'ProductsTranslations.meta_description !=' => '',
        ]);
    }

    /**
     * Get available locales for a product
     *
     * @param string $productId
     * @return array
     */
    public function getAvailableLocales(string $productId): array
    {
        return $this->find()
            ->select(['locale'])
            ->where(['id' => $productId])
            ->orderBy(['locale' => 'ASC'])
            ->toArray();
    }

    /**
     * Get translation statistics
     *
     * @param string $productId
     * @return array
     */
    public function getTranslationStats(string $productId): array
    {
        $total = $this->find()->where(['id' => $productId])->count();
        $complete = $this->find('complete')->where(['id' => $productId])->count();
        $withSeo = $this->find('withSeo')->where(['id' => $productId])->count();

        return [
            'total_translations' => $total,
            'complete_translations' => $complete,
            'translations_with_seo' => $withSeo,
            'completion_percentage' => $total > 0 ? round(($complete / $total) * 100, 2) : 0,
            'seo_percentage' => $total > 0 ? round(($withSeo / $total) * 100, 2) : 0,
        ];
    }
}
