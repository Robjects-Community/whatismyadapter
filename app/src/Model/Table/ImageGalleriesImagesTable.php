<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ImageGalleriesImages Model
 *
 * @property \App\Model\Table\ImageGalleriesTable&\Cake\ORM\Association\BelongsTo $ImageGalleries
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\BelongsTo $Images
 * @method \App\Model\Entity\ImageGalleriesImage newEmptyEntity()
 * @method \App\Model\Entity\ImageGalleriesImage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGalleriesImage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ImageGalleriesImage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ImageGalleriesImage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ImageGalleriesImage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ImageGalleriesImage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ImageGalleriesImage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ImageGalleriesImage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGalleriesImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGalleriesImage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGalleriesImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGalleriesImage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGalleriesImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGalleriesImage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ImageGalleriesImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ImageGalleriesImage> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImageGalleriesImagesTable extends Table
{
    use LogTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('image_galleries_images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ImageGalleries', [
            'foreignKey' => 'image_gallery_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Images', [
            'foreignKey' => 'image_id',
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
            ->uuid('image_gallery_id')
            ->requirePresence('image_gallery_id', 'create')
            ->notEmptyString('image_gallery_id');

        $validator
            ->uuid('image_id')
            ->requirePresence('image_id', 'create')
            ->notEmptyString('image_id');

        $validator
            ->integer('position')
            ->requirePresence('position', 'create')
            ->notEmptyString('position');

        $validator
            ->scalar('caption')
            ->allowEmptyString('caption');

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
        $rules->add($rules->existsIn(['image_gallery_id'], 'ImageGalleries'), ['errorField' => 'image_gallery_id']);
        $rules->add($rules->existsIn(['image_id'], 'Images'), ['errorField' => 'image_id']);

        return $rules;
    }

    /**
     * afterSave callback - Queue preview regeneration when images are added to gallery
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery-image association
     * @param \ArrayObject $options Options for the save operation
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Queue preview regeneration for the gallery when images are added
        $this->log(sprintf(
            'ImageGalleriesImagesTable::afterSave triggered for gallery %s, image %s',
            $entity->image_gallery_id,
            $entity->image_id,
        ), 'info', ['group_name' => 'ImageGalleriesImagesTable']);
        $this->queuePreviewRegeneration($entity->image_gallery_id);
        $this->clearGalleryCache($entity->image_gallery_id);
    }

    /**
     * afterDelete callback - Queue preview regeneration when images are removed from gallery
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @param \Cake\Datasource\EntityInterface $entity The gallery-image association
     * @param \ArrayObject $options Options for the delete operation
     * @return void
     */
    public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Queue preview regeneration for the gallery when images are removed
        $this->queuePreviewRegeneration($entity->image_gallery_id);
        $this->clearGalleryCache($entity->image_gallery_id);
    }

    /**
     * Reorder images within a gallery
     *
     * @param string $galleryId The gallery ID
     * @param array $imageIds Array of image IDs in the desired order
     * @return bool Success
     */
    public function reorderImages(string $galleryId, array $imageIds): bool
    {
        $connection = $this->getConnection();

        $result = $connection->transactional(function () use ($galleryId, $imageIds) {
            foreach ($imageIds as $position => $imageId) {
                $this->updateAll(
                    ['position' => $position],
                    [
                        'image_gallery_id' => $galleryId,
                        'image_id' => $imageId,
                    ],
                );
            }

            return true;
        });

        // Queue preview regeneration and clear gallery cache after reordering
        if ($result) {
            $this->queuePreviewRegeneration($galleryId);
            $this->clearGalleryCache($galleryId);
        }

        return $result;
    }

    /**
     * Get the next available position for a gallery
     *
     * @param string $galleryId The gallery ID
     * @return int The next position
     */
    public function getNextPosition(string $galleryId): int
    {
        $query = $this->find()
            ->where(['image_gallery_id' => $galleryId])
            ->select(['max_position' => $this->find()->func()->max('position')]);

        $result = $query->first();

        return $result && $result->max_position !== null ? (int)$result->max_position + 1 : 0;
    }

    /**
     * Custom finder to get images ordered by position
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query object
     * @param array $options Options array
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findOrdered(SelectQuery $query, array $options): SelectQuery
    {
        return $query->orderBy(['ImageGalleriesImages.position' => 'ASC']);
    }

    /**
     * Queue preview regeneration for a gallery
     *
     * @param string $galleryId Gallery ID
     * @return void
     */
    private function queuePreviewRegeneration(string $galleryId): void
    {
        // Get the ImageGalleries table and call its preview generation method
        $imageGalleriesTable = FactoryLocator::get('Table')->get('ImageGalleries');
        $imageGalleriesTable->queuePreviewGeneration($galleryId);
    }

    /**
     * Clear gallery placeholder cache for both admin and public contexts
     *
     * @param string $galleryId Gallery ID
     * @return void
     */
    private function clearGalleryCache(string $galleryId): void
    {
        // Clear both public and admin gallery caches
        Cache::delete("gallery_placeholder_{$galleryId}", 'default');
        Cache::delete("gallery_placeholder_admin_{$galleryId}", 'default');

        // Clear article cache to update articles containing this gallery
        // This ensures articles immediately reflect gallery image changes
        Cache::clear('content');
        $this->log(
            sprintf('Cleared article cache due to gallery %s image changes', $galleryId),
            'info',
            ['group_name' => 'ImageGalleriesImagesTable'],
        );
    }
}
