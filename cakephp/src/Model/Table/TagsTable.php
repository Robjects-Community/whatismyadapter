<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \App\Model\Table\ArticlesTable&\Cake\ORM\Association\BelongsToMany $Articles
 * @method \App\Model\Entity\Tag newEmptyEntity()
 * @method \App\Model\Entity\Tag newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Tag> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tag get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Tag findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Tag> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tag|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Tag saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Tag>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Tag> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table
{
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

        $this->setTable('tags');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Orderable', [
            'displayField' => 'title',
        ]);

        $this->belongsTo('ParentTag', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id',
        ]);

        $this->addBehavior('Slug');

        $this->addBehavior('Translate', [
            'fields' => [
                'title',
                'description',
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

        $this->belongsToMany('Articles', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'article_id',
            'joinTable' => 'articles_tags',
        ]);

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Tags/image/',
            'field' => 'image',
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
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        return $validator;
    }

    /**
     * After save callback.
     *
     * This method is triggered after a tag entity is saved. It queues a TagSeoUpdateJob
     * if AI settings are enabled.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The tag entity that was saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return void
     * @throws \Exception If there is an error while queueing the SEO update job.
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // noMessage flag will be true if save came from a Job (stops looping)
        $noMessage = $options['noMessage'] ?? false;
        if (SettingsManager::read('AI.enabled') && !$noMessage) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            if (SettingsManager::read('AI.tagSEO') && !empty($this->emptySeoFields($entity))) {
                $this->queueJob('App\Job\TagSeoUpdateJob', $data);
            }

            if (SettingsManager::read('AI.tagTranslations')) {
                $this->queueJob('App\Job\TranslateTagJob', $data);
            }
        }
    }

    /**
     * Get all SEO fields for Tags table (includes description field)
     *
     * @return array<string> List of all SEO field names for tags
     */
    protected function getAllSeoFields(): array
    {
        return array_merge(['description'], $this->getStandardSeoFields());
    }

    /**
     * Retrieves a simple threaded array of tags.
     *
     * This method performs a 'threaded' find operation on the current model, selecting
     * the 'id', 'title', and 'parent_id' fields. It then processes the result set to
     * create an associative array where each key is a tag title and its value is an
     * array of titles of its direct children.
     *
     * @return array An associative array where keys are tag titles and values are arrays
     *               of titles of their direct children.
     */
    public function getSimpleThreadedArray(): array
    {
        return $this->find('threaded')
        ->select(['id', 'title', 'parent_id'])
        ->all()
        ->reduce(function ($accumulator, $tag) {
            $childrenTitles = array_map(function ($child) {
                return $child->title;
            }, $tag->children);
            $accumulator[$tag->title] = $childrenTitles;

            return $accumulator;
        }, []);
    }

    /**
     * Retrieves root tags from the database.
     *
     * This method fetches tags that have no parent (i.e., root tags) and allows for additional conditions
     * to be applied to the query. The results are ordered by the 'lft' field in ascending order and cached
     * using the specified cache key.
     *
     * @param string $cacheKey The key used for caching the query results. The cache key is appended with 'root_tags'.
     * @param array $additionalConditions Optional array of additional query conditions to apply
     * @return array<\App\Model\Entity\Tag> List of Tag entities that are root level (no parent)
     * @throws \Cake\Database\Exception\DatabaseException When there is a database error
     * @throws \RuntimeException When the cache engine is not properly configured
     */
    public function getRootTags(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Tags.parent_id IS' => null,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'root_tags', 'content');

        $results = $query->all()->toList();

        return $results;
    }

    /**
     * Retrieves tags that are marked for display in the main menu.
     *
     * This method fetches all tags that have main_menu flag set to true, ordered by their
     * tree position (lft). Results are cached using the provided cache key. Additional
     * conditions can be merged with the base conditions if needed.
     *
     * @param string $cacheKey The base cache key to use for caching the results
     * @param array $additionalConditions Optional array of additional query conditions to apply
     * @return array<\App\Model\Entity\Tag> List of Tag entities matching the criteria
     * @throws \Cake\Database\Exception\DatabaseException When there is a database error
     * @throws \RuntimeException When the cache engine is not properly configured
     */
    public function getMainMenuTags(string $cacheKey, array $additionalConditions = []): array
    {
        $conditions = [
            'Tags.main_menu' => 1,
        ];
        $conditions = array_merge($conditions, $additionalConditions);
        $query = $this->find()
            ->where($conditions)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . 'main_menu_tags', 'content');

        $results = $query->all()->toList();

        return $results;
    }
}
