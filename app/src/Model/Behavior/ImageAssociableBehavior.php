<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;

/**
 * ImageAssociable Behavior
 *
 * This behavior allows models to associate multiple images with their entities.
 * It provides functionality for saving new image associations and unlinking existing ones.
 * It uses the Images model so you get the resizing and AI analysis for free!
 */
class ImageAssociableBehavior extends Behavior
{
    /**
     * Initialize method
     *
     * Sets up the belongsToMany association with the Images table.
     *
     * @param array $config The configuration settings for the behavior.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_table->belongsToMany('Images', [
            'foreignKey' => 'foreign_key',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'models_images',
            'conditions' => ['ModelsImages.model' => $this->_table->getAlias()],
        ]);
    }

    /**
     * After save callback
     *
     * Handles saving new image uploads and unlinking images after an entity is saved.
     *
     * @param \Cake\Event\EventInterface $event The afterSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!empty($entity->imageUploads)) {
            $this->saveImages($entity);
        }

        if (!empty($entity->unlinkedImages)) {
            $this->unlinkImages($entity, $entity->unlinkedImages);
        }
    }

    /**
     * Save images associated with an entity
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to associate images with.
     * @return void
     */
    protected function saveImages(EntityInterface $entity): void
    {
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        $modelsImagesTable = TableRegistry::getTableLocator()->get('ModelsImages');

        foreach ($entity->imageUploads as $image) {
            $imageEntity = $imagesTable->newEntity([
                'file' => $image,
                'name' => $image->getClientFilename(),
            ]);
            if ($imagesTable->save($imageEntity)) {
                $modelsImagesTable->save($modelsImagesTable->newEntity([
                    'model' => $this->_table->getAlias(),
                    'foreign_key' => $entity->id,
                    'image_id' => $imageEntity->id,
                ]));
            }
        }
    }

    /**
     * Unlink images from an entity
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to unlink images from.
     * @param array $imageIds The IDs of images to unlink.
     * @return void
     */
    public function unlinkImages(EntityInterface $entity, array $imageIds): void
    {
        $imageIds = array_filter($imageIds, function ($value) {
            return $value !== '0';
        });

        if (empty($imageIds)) {
            return;
        }

        $modelsImagesTable = TableRegistry::getTableLocator()->get('ModelsImages');

        foreach ($imageIds as $imageId) {
            $modelsImagesTable->deleteAll([
                'model' => $this->_table->getAlias(),
                'foreign_key' => $entity->id,
                'image_id' => $imageId,
            ]);
        }
    }
}
