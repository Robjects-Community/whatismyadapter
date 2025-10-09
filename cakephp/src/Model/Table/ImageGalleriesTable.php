<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Exception;

/**
 * ImageGalleries Model
 *
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\BelongsToMany $Images
 * @method \App\Model\Entity\ImageGallery newEmptyEntity()
 * @method \App\Model\Entity\ImageGallery newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGallery> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ImageGallery get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ImageGallery findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ImageGallery patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGallery> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ImageGallery|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ImageGallery saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGallery>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGallery> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImageGalleriesTable extends Table
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

        $this->setTable('image_galleries');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Slug', [
            'sourceField' => 'name',
            'targetField' => 'slug',
            'maxLength' => 255,
        ]);
        $this->addBehavior('Translate', [
            'fields' => [
                'name',
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

        $this->hasMany('ImageGalleriesImages', [
            'foreignKey' => 'image_gallery_id',
            'dependent' => true,
        ]);

        $this->belongsToMany('Images', [
            'foreignKey' => 'image_gallery_id',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'image_galleries_images',
            'through' => 'ImageGalleriesImages',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->allowEmptyString('slug');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->boolean('is_published')
            ->notEmptyString('is_published');

        $validator
            ->uuid('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->uuid('modified_by')
            ->allowEmptyString('modified_by');

        $validator
            ->scalar('meta_title')
            ->maxLength('meta_title', 255)
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
        $rules->add($rules->isUnique(['slug']), ['errorField' => 'slug']);

        return $rules;
    }

    /**
     * afterSave callback - Queue preview generation job when gallery changes
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery entity
     * @param \ArrayObject $options Options for the save operation
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // noMessage flag will be true if save came from a Job (stops looping)
        $noMessage = $options['noMessage'] ?? false;

        // Queue preview generation for new galleries or when images might have changed
        if ($entity->isNew() || $entity->isDirty('name') || $entity->isDirty('description')) {
            $this->queuePreviewGeneration($entity->id);
        }

        // Clear article cache when gallery published status changes
        // This ensures articles show/hide galleries immediately when status changes
        if ($entity->isDirty('is_published')) {
            Cache::clear('content');
            $this->log(
                sprintf('Cleared article cache due to gallery %s publish status change', $entity->id),
                'info',
                ['group_name' => 'ImageGalleriesTable'],
            );
        }

        // Queue AI jobs for published galleries when AI is enabled
        if (
            $entity->is_published
            && SettingsManager::read('AI.enabled')
            && !$noMessage
        ) {
            $data = [
                'id' => $entity->id,
                'name' => $entity->name,
            ];

            // Queue SEO generation job if SEO setting is enabled and there are empty SEO fields
            if (SettingsManager::read('AI.gallerySEO') && !empty($this->emptySeoFields($entity))) {
                $this->queueJob('App\\Job\\ImageGallerySeoUpdateJob', $data);
            }

            // Queue translation job if translations are enabled
            if (SettingsManager::read('AI.galleryTranslations', false)) {
                $this->queueJob('App\\Job\\TranslateImageGalleryJob', $data);
            }
        }
    }

    /**
     * beforeDelete callback - Clean up preview image file
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery entity
     * @param \ArrayObject $options Options for the delete operation
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Clean up preview image file
        if ($entity->preview_image) {
            $previewPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $entity->preview_image;
            if (file_exists($previewPath)) {
                unlink($previewPath);
            }
        }

        // Also clean up gallery ID-based file
        $galleryPreviewPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $entity->id . '.jpg';
        if (file_exists($galleryPreviewPath)) {
            unlink($galleryPreviewPath);
        }
    }

    /**
     * Queue preview generation job for a gallery
     *
     * @param string $galleryId Gallery ID
     * @return void
     */
    public function queuePreviewGeneration(string $galleryId): void
    {
        try {
            $this->queueJob('App\\Job\\GenerateGalleryPreviewJob', [
                'gallery_id' => $galleryId,
            ]);
        } catch (Exception $e) {
            $this->log(
                sprintf('Failed to queue preview generation for gallery %s: %s', $galleryId, $e->getMessage()),
                'error',
                ['group_name' => 'App\\Model\\Table\\ImageGalleriesTable'],
            );
        }
    }

    /**
     * Get a gallery for placeholder rendering with caching
     *
     * @param string $galleryId Gallery UUID
     * @param bool $requirePublished Whether to require the gallery to be published (default: true)
     * @param string|null $cacheKey Locale-aware cache key from controller
     * @return \App\Model\Entity\ImageGallery|null Gallery entity or null if not found
     */
    public function getGalleryForPlaceholder(
        string $galleryId,
        bool $requirePublished = true,
        ?string $cacheKey = null,
    ): ?object {
        // Generate locale-aware cache key if provided, otherwise fall back to static key
        if ($cacheKey) {
            $baseKey = $requirePublished
                ? "gallery_placeholder_{$galleryId}"
                : "gallery_placeholder_admin_{$galleryId}";
            $finalCacheKey = $baseKey . $cacheKey;
        } else {
            $finalCacheKey = $requirePublished
                ? "gallery_placeholder_{$galleryId}"
                : "gallery_placeholder_admin_{$galleryId}";
        }

        // Debug: Log cache key and locale being used
        $this->log(
            sprintf('ImageGalleriesTable: Using cache key %s with locale %s', $finalCacheKey, $this->getLocale()),
            'debug',
        );

        $conditions = ['ImageGalleries.id' => $galleryId];
        if ($requirePublished) {
            $conditions['ImageGalleries.is_published'] = true;
        }

        return $this->find()
            ->cache($finalCacheKey, 'default')
            ->contain([
                'Images' => function ($query) {
                    return $query->where([
                        'Images.image IS NOT' => null,
                        'Images.image !=' => '',
                    ])
                    ->orderBy(['ImageGalleriesImages.position' => 'ASC']);
                },
            ])
            ->where($conditions)
            ->first();
    }

    /**
     * Check if gallery images have changed since last save
     *
     * @param \Cake\Datasource\EntityInterface $entity Gallery entity
     * @return bool True if images have changed
     */
    private function imagesChanged(EntityInterface $entity): bool
    {
        // This is a basic check - in practice, image changes happen via the junction table
        // The ImageGalleriesImagesTable will handle queuing preview regeneration
        return $entity->isDirty('images') || $entity->isDirty('_joinData');
    }
}
