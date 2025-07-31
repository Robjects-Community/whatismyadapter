<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use DateTime;

/**
 * Products Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $ParentProducts
 * @property \App\Model\Table\ProductsTable&\Cake\ORM\Association\HasMany $ChildProducts
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsToMany $Tags
 * @property \App\Model\Table\ProductCategoriesTable&\Cake\ORM\Association\BelongsToMany $Categories
 * @property \App\Model\Table\ProductSpecificationsTable&\Cake\ORM\Association\HasMany $ProductSpecifications
 * @property \App\Model\Table\ProductConnectorsTable&\Cake\ORM\Association\HasMany $ProductConnectors
 * @property \App\Model\Table\ProductReviewsTable&\Cake\ORM\Association\HasMany $ProductReviews
 * @property \App\Model\Table\ProductVerificationsTable&\Cake\ORM\Association\HasMany $ProductVerifications
 * @property \App\Model\Table\ProductAffiliateLinksTable&\Cake\ORM\Association\HasMany $ProductAffiliateLinks
 * @property \App\Model\Table\ProductViewsTable&\Cake\ORM\Association\HasMany $ProductViews
 *
 * @method \App\Model\Entity\Product newEmptyEntity()
 * @method \App\Model\Entity\Product newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Product> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Product get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Product findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Product patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Product> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Product|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Product saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Product>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Product>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Product>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Product> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Product>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Product>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Product>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Product> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProductsTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use QueueableJobsTrait;
    use SeoFieldsTrait;
    use TranslateTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products'); // Set the table name
        $this->setDisplayField('title'); // Set the display field for the table
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

         $this->addBehavior('Commentable');

        $this->addBehavior('Orderable', [
            'displayField' => 'title',
        ]); // Add Orderable behavior with display field set to 'title'

        $this->addBehavior(name: 'Slug');

        $this->addBehavior('Tree');
        // $this->addBehavior('Slug', [
        //     'field' => 'title',
        //     'slug' => 'slug',
        //     'replacement' => '-',
        // ]);
        $this->addBehavior('ImageAssociable');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Products/image/',
            'field' => 'image',
        ]);

        //// Add Translate behavior for multilingual support ////
        $this->addBehavior('Translate', [
            'fields' => [
                'title', 'lede', 'body', 'summary',
                'meta_title', 'meta_description', 'meta_keywords',
                'facebook_description', 'linkedin_description',
                'instagram_description', 'twitter_description',
            ],
        ]);

        //// Define associations ////
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT', // Use LEFT join to allow products without a user - this is useful for products that may not have a user associated with them, such as system-generated products or products created by automated processes
        ]);

       // Tags association - this uses a unique junction table for many-to-many relationship which allows products to have multiple tags and tags to be associated with different types of entities such as articles, products, etc.
       // with this setup, you can easily add high-level tags to any entity, including articles, products, etc. which would be useful for categorizing and filtering content across different types of entities.
       // TODO: Incorporate TAGS INTO all kinds of entities
       $this->belongsToMany('Tags', [
           'foreignKey' => 'product_id', // Foreign key is 'product_id'
           // this means that the product_id in the junction table (the 'joinTable' in this case) contains the foreign key that references the Products table
           'targetForeignKey' => 'tag_id', // Foreign key in the separate table listed in the 'joinTable' 
           'joinTable' => 'products_tags', // This is the junction table that connects products and tags; if the 'joinTable' is not specified, CakePHP will automatically create a junction table named 'products_tags' based on the foreign keys
           'saveStrategy' => 'replace', // This will replace existing tags with the new ones
           'dependent' => true, // This ensures that if a product is deleted, its associated tags in the junction table are also deleted - this is useful for maintaining data integrity and ensuring that there are no orphaned records in the junction table
           'cascadeCallbacks' => true, // This ensures that any callbacks defined in the TagsTable are executed when saving or deleting tags - for example, if you have a beforeDelete callback in the TagsTable that performs some action when a tag is deleted, it will be executed when the product is deleted
       ]);

        /////// Product Hierarchy ///////
         $this->belongsTo('ParentProducts', [
            'className' => 'Products',
            'foreignKey' => 'parent_id',
        ]);

        $this->hasMany('ChildProducts', [
            'className' => 'Products', 
            'foreignKey' => 'parent_id',
        ]);
        ////////// Product Specific Associations //////////

        // Product Categories - this allows products to be associated with multiple categories, which can be useful for filtering and organizing products in the application - this would be similar to how tags work, but will cater to specific product types for now 
        // TODO: Incorporate CATEGORIES OR TAGS INTO all kinds of entities if possible and/or necessary for implementation of DRY principles
        $this->belongsToMany('Categories', [
            'className' => 'ProductCategories',
            'foreignKey' => 'product_id',
            'targetForeignKey' => 'category_id',
            'joinTable' => 'products_categories',
        ]);


        $this->hasMany('ProductSpecifications', [
            'foreignKey' => 'product_id',
            'dependent' => true, // Ensure product specifications are deleted when product is deleted
            'cascadeCallbacks' => true, // Ensure product specifications are deleted when product is deleted
        ]);


        // TODO: Product Connectors - this allows products to have multiple connectors, which can be useful for products that support multiple connection types or standards
        $this->hasMany('ProductConnectors', [
            'foreignKey' => 'product_id',

        ]);

        $this->hasMany('ProductReviews', [
            'foreignKey' => 'product_id',
            'dependent' => true, // Ensure product reviews are deleted when product is deleted
            'cascadeCallbacks' => true, // Ensure product reviews are deleted when product is deleted
        ]);

        $this->hasMany('ProductVerifications', [
            'foreignKey' => 'product_id',
            'dependent' => true, // Ensure product verifications are deleted when product is deleted
            'cascadeCallbacks' => true, // Ensure product verifications are deleted when product is deleted
        ]);

        $this->hasMany('ProductAffiliateLinks', [
            'foreignKey' => 'product_id',
            'dependent' => true, // Ensure product affiliate links are deleted when product is deleted
            'cascadeCallbacks' => true, // Ensure product affiliate links are deleted when product is deleted
        ]);

        $this->hasMany('ProductViews', [
            'foreignKey' => 'product_id',
            'dependent' => true, // Ensure product views are deleted when product is deleted
            'cascadeCallbacks' => true, // Ensure product views are deleted when product is deleted
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
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('kind')
            ->maxLength('kind', 20)
            ->notEmptyString('kind');

        $validator
            ->boolean('featured')
            ->notEmptyString('featured');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('lede')
            ->maxLength('lede', 400)
            ->allowEmptyString('lede');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 191)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug');

        // Only add the unique rule if it doesn't already exist
        if ( !$validator->hasField('slug') ) {
            $validator->add('slug', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'This slug is already in use.'
            ]);
        }

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $validator
            ->scalar('manufacturer')
            ->maxLength('manufacturer', 255)
            ->allowEmptyString('manufacturer');

        $validator
            ->scalar('model_number')
            ->maxLength('model_number', 255)
            ->allowEmptyString('model_number');

        $validator
            ->decimal('price')
            ->greaterThanOrEqual('price', 0)
            ->allowEmptyString('price');

        $validator
            ->scalar('currency')
            ->maxLength('currency', 3)
            ->allowEmptyString('currency');

        $validator
            ->decimal('reliability_score')
            ->range('reliability_score', [0, 5])
            ->allowEmptyString('reliability_score');

        $validator
            ->boolean('is_published')
            ->allowEmptyString('is_published');

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
        // buildRules method is used to define rules for the table, such as ensuring that certain fields are unique or that foreign keys exist in their respective tables
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']); // Ensure the user exists in the Users table
        // $rules->add($rules->existsIn('parent_id', 'ParentProducts'), ['errorField' => 'parent_id']); // Ensure the parent product exists
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']); // Ensure the slug is unique 

        return $rules;
    }

    /**
     * Before save callback
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Set published date when product is published
        if ($entity->isDirty('is_published') && $entity->is_published) {
            $entity->published = new DateTime('now');
        }

        // Calculate word count if body is set or modified
        if ($entity->isDirty('body') || ($entity->isNew() && !empty($entity->body))) {
            $strippedBody = strip_tags((string)$entity->body);
            $wordCount = str_word_count($strippedBody);
            $entity->word_count = $wordCount;
        }
    }

    /**
     * Find published products
     */
    public function findPublished($query, $options)
    {
        return $query->where([
            'Products.is_published' => 1,
            'Products.verification_status' => 'approved',
        ]);
    }

    /**
     * Reorder products in tree structure
     */
    public function reorder(array $data): bool
    {
        $product = $this->get($data['id']);
        
        if ($data['newParentId'] === 'root') {
            $product->parent_id = null;
        } else {
            $product->parent_id = $data['newParentId'];
        }

        return $this->save($product) !== false;
    }

    ///// TODO: Implement additional methods for product-specific functionality, such as managing product specifications, connectors, reviews, etc. /////
    //  /**
    //  * After save callback
    //  *
    //  * Handles AI-powered enhancements including:
    //  * - Article tagging
    //  * - Summary generation
    //  * - SEO field population
    //  * - Content translation
    //  *
    //  * @param \Cake\Event\EventInterface $event The afterSave event that was fired
    //  * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
    //  * @param \ArrayObject $options The options passed to the save method
    //  * @return void
    //  */
    // public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    // {
    //     // noMessage flag will be true if save came from a Job (stops looping)
    //     $noMessage = $options['noMessage'] ?? false;

    //     // All Articles should be tagged from the start
    //     if (
    //         SettingsManager::read('AI.enabled')
    //         && !$noMessage
    //     ) {
    //         $data = [
    //             'id' => $entity->id,
    //             'title' => $entity->title,
    //         ];

    //         if (
    //             $entity->kind == 'article' &&
    //             ((isset($options['regenerateTags']) &&
    //             $options['regenerateTags'] == 1) ||
    //             !isset($options['regenerateTags']))
    //         ) {
    //             // Queue up an ArticleTagUpdateJob
    //             if (SettingsManager::read('AI.articleTags')) {
    //                 $this->queueJob('App\Job\ArticleTagUpdateJob', $data);
    //             }
    //         }

    //         // Queue up an ArticleSummaryUpdateJob
    //         if (SettingsManager::read('AI.articleSummaries') && empty($entity->summary)) {
    //             $this->queueJob('App\Job\ArticleSummaryUpdateJob', $data);
    //         }
    //     }

    //     // Published Articles should be SEO ready with translations
    //     if (
    //         $entity->is_published
    //         && SettingsManager::read('AI.enabled')
    //         && !$noMessage
    //     ) {
    //         $data = [
    //             'id' => $entity->id,
    //             'title' => $entity->title,
    //         ];

    //         // Queue a job to update the Article SEO fields
    //         if (SettingsManager::read('AI.articleSEO') && !empty($this->emptySeoFields($entity))) {
    //             $this->queueJob('App\Job\ArticleSeoUpdateJob', $data);
    //         }

    //         // Queue a job to translate the Article
    //         if (SettingsManager::read('AI.articleTranslations')) {
    //             $this->queueJob('App\Job\TranslateArticleJob', $data);
    //         }
    //     }
    // }

    // /**
    //  * Retrieves a list of featured articles with optional additional conditions.
    //  *
    //  * This method constructs a query to find articles that are marked as featured.
    //  * Additional conditions can be provided to further filter the results.
    //  * The results are ordered by the 'lft' field in ascending order.
    //  *
    //  * @param array $additionalConditions An array of additional conditions to apply to the query.
    //  * @return array A list of featured articles that match the specified conditions.
    //  */
    // public function getFeatured(string $cacheKey, array $additionalConditions = []): array
    // {
    //     $conditions = [
    //         'Articles.kind' => 'article',
    //         'Articles.featured' => 1,
    //         'Articles.is_published' => 1,
    //     ];
    //     $conditions = array_merge($conditions, $additionalConditions);
    //     $query = $this->find()
    //         ->where($conditions)
    //         ->orderBy(['lft' => 'ASC'])
    //         ->cache($cacheKey . 'featured_articles', 'content');

    //     $results = $query->all()->toList();

    //     return $results;
    // }

    // /**
    //  * Retrieves a list of root pages from the Articles table.
    //  *
    //  * This method fetches articles that are categorized as 'page', have no parent (i.e., root pages),
    //  * and are published. Additional conditions can be provided to further filter the results.
    //  *
    //  * @param array $additionalConditions An associative array of additional conditions to apply to the query.
    //  *                                    These conditions will be merged with the default conditions.
    //  * @return array An array of root pages that match the specified conditions,
    //  * ordered by the 'lft' field in ascending order.
    //  */
    // public function getRootPages(string $cacheKey, array $additionalConditions = []): array
    // {
    //     $conditions = [
    //         'Articles.kind' => 'page',
    //         'Articles.parent_id IS' => null,
    //         'Articles.is_published' => 1,
    //     ];
    //     $conditions = array_merge($conditions, $additionalConditions);
    //     $query = $this->find()
    //         ->where($conditions)
    //         ->orderBy(['lft' => 'ASC'])
    //         ->cache($cacheKey . 'root_pages', 'content');

    //     $results = $query->all()->toList();

    //     return $results;
    // }

    // /**
    //  * Retrieves published pages marked for display in the main menu.
    //  *
    //  * This method fetches articles that meet the following criteria:
    //  * - Are of type 'page'
    //  * - Are published (is_published = 1)
    //  * - Are marked for main menu display (main_menu = 1)
    //  * Results are ordered by the 'lft' field for proper tree structure display.
    //  * Results are cached using the 'main_menu_pages' key in the 'content' cache config.
    //  *
    //  * @param array $additionalConditions Additional conditions to merge with the default query conditions
    //  * @return array List of Article entities matching the criteria
    //  * @throws \Cake\Database\Exception\DatabaseException When the database query fails
    //  * @throws \Cake\Cache\Exception\InvalidArgumentException When cache configuration is invalid
    //  */
    // public function getMainMenuPages(string $cacheKey, array $additionalConditions = []): array
    // {
    //     $conditions = [
    //         'Articles.kind' => 'page',
    //         'Articles.is_published' => 1,
    //         'Articles.main_menu' => 1,
    //     ];
    //     $conditions = array_merge($conditions, $additionalConditions);
    //     $query = $this->find()
    //         ->where($conditions)
    //         ->orderBy(['lft' => 'ASC'])
    //         ->cache($cacheKey . 'main_menu_pages', 'content');

    //     $results = $query->all()->toList();

    //     return $results;
    // }

    // /**
    //  * Gets an array of years and months that have published articles.
    //  *
    //  * This method queries the articles table to find all unique year/month combinations
    //  * where articles were published, organizing them in a hierarchical array structure
    //  * with years as keys and months as values. Results are cached using the 'content'
    //  * cache configuration to improve performance.
    //  *
    //  * @return array An array where keys are years and values are arrays of month numbers
    //  *              that have published articles, sorted in descending order.
    //  */
    // public function getArchiveDates(string $cacheKey): array
    // {
    //     $query = $this->find()
    //         ->select([
    //             'year' => 'YEAR(published)',
    //             'month' => 'MONTH(published)',
    //         ])
    //         ->where([
    //             'Articles.is_published' => 1,
    //             'Articles.kind' => 'article',
    //             'Articles.published IS NOT' => null,
    //         ])
    //         ->groupBy(['year', 'month'])
    //         ->orderBy([
    //             'year' => 'DESC',
    //             'month' => 'DESC',
    //         ])
    //         ->cache($cacheKey . 'archive_dates', 'content');

    //     $dates = [];
    //     foreach ($query as $result) {
    //         $year = $result->year;
    //         if (!isset($dates[$year])) {
    //             $dates[$year] = [];
    //         }
    //         $dates[$year][] = (int)$result->month;
    //     }

    //     return $dates;
    // }

    // /**
    //  * Retrieves the most recent published articles.
    //  *
    //  * This method queries the Articles table to find articles that are of kind 'article' and are published.
    //  * It includes associated Users and Tags data, orders the results by the published date in descending order,
    //  * and limits the results to the top 3 most recent articles.
    //  *
    //  * @return array An array of the most recent published articles, including associated Users and Tags data.
    //  */
    // public function getRecentArticles(string $cacheKey, array $additionalConditions = []): array
    // {
    //     $conditions = [
    //         'Articles.kind' => 'article',
    //         'Articles.is_published' => 1,
    //     ];
    //     $conditions = array_merge($conditions, $additionalConditions);

    //     $query = $this->find()
    //         ->where($conditions)
    //         ->contain(['Users', 'Tags'])
    //         ->orderBy(['Articles.published' => 'DESC'])
    //         ->limit(3)
    //         ->cache($cacheKey . 'recent_articles', 'content');

    //     return $query->all()->toArray();
    // }

}
