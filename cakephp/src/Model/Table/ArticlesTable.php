<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use App\Model\Table\ArticleImageDetectionTrait;
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
 * Articles Table
 *
 * Manages article content with features including:
 * - Multi-language support
 * - SEO metadata
 * - Image handling
 * - Commenting system
 * - Page view tracking
 * - AI-powered content enhancement
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 * @property \Cake\ORM\Association\HasMany $PageViews
 * @property \Cake\ORM\Association\HasMany $Slugs
 * @method \App\Model\Entity\Article newEmptyEntity()
 * @method \App\Model\Entity\Article newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Article[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Article get($primaryKey, $options = [])
 * @method \App\Model\Entity\Article findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Article patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Article[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Article|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 */
class ArticlesTable extends Table
{
    use ArticleImageDetectionTrait;
    use ImageValidationTrait;
    use LogTrait;
    use QueueableJobsTrait;
    use SeoFieldsTrait;
    use TranslateTrait;

    /**
     * Initialize method
     *
     * Configures table associations, behaviors, and other settings
     *
     * @param array<string, mixed> $config Configuration array
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Commentable');

        $this->addBehavior('Orderable', [
            'displayField' => 'title',
        ]);

        $this->addBehavior('Slug');

        $this->addBehavior('ImageAssociable');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Articles/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Translate', [
            'fields' => [
                'title',
                'body',
                'summary',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'facebook_description',
                'linkedin_description',
                'instagram_description',
                'twitter_description',
            ],
            'defaultLocale' => 'en_GB',
            'allowEmptyTranslations' => false,
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'article_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'articles_tags',
        ]);

        $this->hasMany('PageViews', [
            'foreignKey' => 'article_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        // Add to initialize() method in ArticlesTable - for simple-products-REFACTORING-plan.md
        $this->hasMany('Products', [
            'foreignKey' => 'article_id',
            'dependent' => false, // Don't delete products when article is deleted
        ]);
    }

    /**
     * Default validation rules
     *
     * Sets up validation rules for article fields including:
     * - User ID validation
     * - Title requirements
     * - Body content validation
     * - Image upload restrictions
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('body')
            ->allowEmptyString('body');

        $this->addOptionalImageValidation($validator, 'image');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating application integrity
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Before save callback
     *
     * Handles:
     * - Setting publication date when article is published
     * - Calculating word count for article body
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Check if is_published has changed to published
        if ($entity->isDirty('is_published') && $entity->is_published) {
            $entity->published = new DateTime('now');
        }

        // Calculate word count if body is set or modified
        if ($entity->isDirty('body') || ($entity->isNew() && !empty($entity->body))) {
            $strippedBody = strip_tags((string)$entity->body); // Ensure body is a string
            $wordCount = str_word_count($strippedBody);
            $entity->word_count = $wordCount;
        }
    }

    /**
     * After save callback
     *
     * Handles AI-powered enhancements including:
     * - Article tagging
     * - Summary generation
     * - SEO field population
     * - Content translation
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
     * @param \ArrayObject $options The options passed to the save method
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // noMessage flag will be true if save came from a Job (stops looping)
        $noMessage = $options['noMessage'] ?? false;

        // All Articles should be tagged from the start
        if (
            SettingsManager::read('AI.enabled')
            && !$noMessage
        ) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            if (
                $entity->kind == 'article' &&
                ((isset($options['regenerateTags']) &&
                    $options['regenerateTags'] == 1) ||
                    !isset($options['regenerateTags']))
            ) {
                // Queue up an ArticleTagUpdateJob
                if (SettingsManager::read('AI.articleTags')) {
                    $this->queueJob('App\Job\ArticleTagUpdateJob', $data);
                }
            }

            // Queue up an ArticleSummaryUpdateJob
            if (SettingsManager::read('AI.articleSummaries') && empty($entity->summary)) {
                $this->queueJob('App\Job\ArticleSummaryUpdateJob', $data);
            }
        }

        // Published Articles should be SEO ready with translations
        if (
            $entity->is_published
            && SettingsManager::read('AI.enabled')
            && !$noMessage
        ) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            // Queue a job to update the Article SEO fields
            if (SettingsManager::read('AI.articleSEO') && !empty($this->emptySeoFields($entity))) {
                $this->queueJob('App\Job\ArticleSeoUpdateJob', $data);
            }

            // Queue a job to translate the Article
            if (SettingsManager::read('AI.articleTranslations')) {
                $this->queueJob('App\Job\TranslateArticleJob', $data);
            }
        }

        // Check for image generation for published articles without images
        if (
            $entity->is_published
            && $entity->kind == 'article'
            && SettingsManager::read('AI.enabled')
            && SettingsManager::read('AI.imageGeneration.enabled')
            && !$noMessage
        ) {
            // Queue image generation job for articles that need images
            $this->queueImageGenerationIfNeeded($entity);
        }
    }

    /**
     * Retrieves a list of featured articles with optional additional conditions.
     *
     * This method constructs a query to find articles that are marked as featured.
     * Additional conditions can be provided to further filter the results.
     * The results are ordered by the 'lft' field in ascending order.
     *
     * @param array $additionalConditions An array of additional conditions to apply to the query.
     * @return array A list of featured articles that match the specified conditions.
     */
    public function getFeatured(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'article',
            'Articles.featured' => 1,
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'featured_articles', 'content');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves a list of root pages from the Articles table.
     *
     * This method fetches articles that are categorized as 'page', have no parent (i.e., root pages),
     * and are published. Additional conditions can be provided to further filter the results.
     *
     * @param array $additionalConditions An associative array of additional conditions to apply to the query.
     *                                    These conditions will be merged with the default conditions.
     * @return array An array of root pages that match the specified conditions,
     * ordered by the 'lft' field in ascending order.
     */
    public function getRootPages(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
            'Articles.parent_id IS' => null,
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'root_pages', 'content');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves published pages marked for display in the main menu.
     *
     * This method fetches articles that meet the following criteria:
     * - Are of type 'page'
     * - Are published (is_published = 1)
     * - Are marked for main menu display (main_menu = 1)
     * Results are ordered by the 'lft' field for proper tree structure display.
     * Results are cached using the 'main_menu_pages' key in the 'content' cache config.
     *
     * @param array $additionalConditions Additional conditions to merge with the default query conditions
     * @return array List of Article entities matching the criteria
     * @throws \Cake\Database\Exception\DatabaseException When the database query fails
     * @throws \Cake\Cache\Exception\InvalidArgumentException When cache configuration is invalid
     */
    public function getMainMenuPages(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
            'Articles.is_published' => 1,
            'Articles.main_menu' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'main_menu_pages', 'content');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves published pages marked for display in the footer menu.
     *
     * This method fetches articles that meet the following criteria:
     * - Are of type 'page'
     * - Are published (is_published = 1)
     * - Are marked for footer menu display (footer_menu = 1)
     * Results are ordered by the 'lft' field for proper tree structure display.
     * Results are cached using the 'footer_menu_pages' key in the 'content' cache config.
     *
     * @param array $additionalConditions Additional conditions to merge with the default query conditions
     * @return array List of Article entities matching the criteria
     * @throws \Cake\Database\Exception\DatabaseException When the database query fails
     * @throws \Cake\Cache\Exception\InvalidArgumentException When cache configuration is invalid
     */
    public function getFooterMenuPages(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'page',
            'Articles.is_published' => 1,
            'Articles.footer_menu' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'footer_menu_pages', 'content');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves published pages marked for display in the footer menu with inheritance.
     *
     * This method extends getFooterMenuPages to include child pages when their parent
     * is marked for footer menu display. Child pages inherit their parent's footer_menu
     * setting unless they have an explicit override.
     *
     * @param array $additionalConditions Additional conditions to merge with the default query conditions
     * @return array List of Article entities matching the criteria with inherited footer menu settings
     */
    public function getFooterMenuPagesWithChildren(string $cacheKey, array $additionalConditions = []): array
    {
        // Get pages with footer_menu = 1 (explicitly set)
        $explicitFooterPages = $this->getFooterMenuPages($cacheKey, $additionalConditions);

        $allFooterPages = [];

        // Add explicitly marked pages and their children
        foreach ($explicitFooterPages as $page) {
            $allFooterPages[] = $page;

            // Get all children of this page
            $children = $this->find()
                ->where([
                    'Articles.kind' => 'page',
                    'Articles.is_published' => 1,
                    'Articles.lft >' => $page->lft,
                    'Articles.rght <' => $page->rght,
                ])
                ->orderBy(['lft' => 'ASC'])
                ->cache($cacheKey . 'footer_children_' . $page->id, 'content')
                ->all();

            foreach ($children as $child) {
                $allFooterPages[] = $child;
            }
        }

        // Remove duplicates based on ID
        $uniquePages = [];
        $seenIds = [];
        foreach ($allFooterPages as $page) {
            if (!in_array($page->id, $seenIds)) {
                $uniquePages[] = $page;
                $seenIds[] = $page->id;
            }
        }

        return $uniquePages;
    }

    /**
     * Retrieves published pages marked for display in the main menu with inheritance.
     *
     * This method extends getMainMenuPages to include child pages when their parent
     * is marked for main menu display. Child pages inherit their parent's main_menu
     * setting unless they have an explicit override.
     *
     * @param array $additionalConditions Additional conditions to merge with the default query conditions
     * @return array List of Article entities matching the criteria with inherited main menu settings
     */
    public function getMainMenuPagesWithChildren(string $cacheKey, array $additionalConditions = []): array
    {
        // Get pages with main_menu = 1 (explicitly set)
        $explicitMainPages = $this->getMainMenuPages($cacheKey, $additionalConditions);

        $allMainPages = [];

        // Add explicitly marked pages and their children
        foreach ($explicitMainPages as $page) {
            $allMainPages[] = $page;

            // Get all children of this page
            $children = $this->find()
                ->where([
                    'Articles.kind' => 'page',
                    'Articles.is_published' => 1,
                    'Articles.lft >' => $page->lft,
                    'Articles.rght <' => $page->rght,
                ])
                ->orderBy(['lft' => 'ASC'])
                ->cache($cacheKey . 'main_children_' . $page->id, 'content')
                ->all();

            foreach ($children as $child) {
                $allMainPages[] = $child;
            }
        }

        // Remove duplicates based on ID
        $uniquePages = [];
        $seenIds = [];
        foreach ($allMainPages as $page) {
            if (!in_array($page->id, $seenIds)) {
                $uniquePages[] = $page;
                $seenIds[] = $page->id;
            }
        }

        return $uniquePages;
    }

    /**
     * Check if a page inherits menu settings from its parent.
     *
     * @param string $pageId The ID of the page to check
     * @param string $menuType Either 'main_menu' or 'footer_menu'
     * @return bool True if the page should inherit from parent, false otherwise
     */
    public function shouldInheritFromParent(string $pageId, string $menuType): bool
    {
        $page = $this->get($pageId);

        if (!$page->parent_id) {
            return false; // Root pages don't inherit
        }

        // Check if the page has an explicit setting (1 = yes, 0 = no, null = inherit)
        $explicitSetting = $page->{$menuType};

        if ($explicitSetting !== null && $explicitSetting !== '') {
            return false; // Page has explicit setting, don't inherit
        }

        // Check if parent is marked for this menu type
        $parent = $this->get($page->parent_id);

        return (bool)$parent->{$menuType};
    }

    /**
     * Gets an array of years and months that have published articles.
     *
     * This method queries the articles table to find all unique year/month combinations
     * where articles were published, organizing them in a hierarchical array structure
     * with years as keys and months as values. Results are cached using the 'content'
     * cache configuration to improve performance.
     *
     * @return array An array where keys are years and values are arrays of month numbers
     *              that have published articles, sorted in descending order.
     */
    public function getArchiveDates(string $cacheKey): array
    {
        $query = $this->find()
            ->select([
                'year' => 'YEAR(published)',
                'month' => 'MONTH(published)',
            ])
            ->where([
                'Articles.is_published' => 1,
                'Articles.kind' => 'article',
                'Articles.published IS NOT' => null,
            ])
            ->groupBy(['year', 'month'])
            ->orderBy([
                'year' => 'DESC',
                'month' => 'DESC',
            ])
            ->cache($cacheKey . 'archive_dates', 'content');

        $dates = [];
        foreach ($query as $result) {
            $year = $result->year;
            if (!isset($dates[$year])) {
                $dates[$year] = [];
            }
            $dates[$year][] = (int)$result->month;
        }

        return $dates;
    }

    /**
     * Retrieves the most recent published articles.
     *
     * This method queries the Articles table to find articles that are of kind 'article' and are published.
     * It includes associated Users and Tags data, orders the results by the published date in descending order,
     * and limits the results to the top 3 most recent articles.
     *
     * @return array An array of the most recent published articles, including associated Users and Tags data.
     */
    public function getRecentArticles(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Articles.kind' => 'article',
            'Articles.is_published' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);

        $query = $this->find()
            ->where($conditions)
            ->contain(['Users', 'Tags'])
            ->orderBy(['Articles.published' => 'DESC'])
            ->limit(3)
            ->cache($cacheKey . 'recent_articles', 'content');

        return $query->all()->toArray();
    }
}
