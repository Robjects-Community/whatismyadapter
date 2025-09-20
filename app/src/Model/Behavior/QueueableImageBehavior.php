<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\Utility\Text;

/**
 * QueueableImage Behavior
 *
 * This behavior handles file uploads, deletion of old files, and queues image processing
 * and AI analysis jobs for uploaded images. It integrates with the Josegonzalez/Upload
 * plugin for file handling.
 */
class QueueableImageBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'folder_path' => 'files/', // Relative path within webroot where images are stored (e.g., 'img/uploads')
        'field' => 'image', // The entity field name that holds the uploaded file.
    ];

    /**
     * Initialize method.
     *
     * Sets up the Josegonzalez/Upload behavior configuration for the image field.
     *
     * @param array<string, mixed> $config The configuration settings provided to the behavior.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $field = $this->getConfig('field');

        // Prepare Upload behavior configuration
        $uploadConfig = [
            $field => [
                'fields' => [
                    'dir' => 'dir', // Field to store the directory info (optional)
                    'size' => 'size', // Field to store the file size
                    'type' => 'mime', // Field to store the MIME type
                ],
                /**
                 * Callback to generate a unique filename for the uploaded file.
                 *
                 * @param \Cake\ORM\Table $table The table instance.
                 * @param \Cake\Datasource\EntityInterface $entity The entity instance.
                 * @param array<string, mixed> $data The uploaded file data.
                 * @param string $field The field name.
                 * @param array<string, mixed> $settings The behavior settings.
                 * @return string The generated unique filename.
                 */
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    $file = $entity->{$field};
                    $clientFilename = $file->getClientFilename();
                    $ext = pathinfo($clientFilename, PATHINFO_EXTENSION);

                    return Text::uuid() . '.' . strtolower($ext);
                },
                /**
                 * Callback to specify paths to delete when an entity is deleted or updated
                 * with a new file.
                 *
                 * @param string $path The base path where the file is stored.
                 * @param \Cake\Datasource\EntityInterface $entity The entity instance.
                 * @param string $field The field name.
                 * @param array<string, mixed> $settings The behavior settings.
                 * @return array<string> An array of file paths to delete.
                 */
                'deleteCallback' => function ($path, $entity, $field, $settings) {
                    $paths = [
                        $path . $entity->{$field}, // Original file path
                    ];

                    // Add paths for all resized versions based on 'ImageSizes' setting
                    $imageSizes = SettingsManager::read('ImageSizes', []);
                    foreach ($imageSizes as $width) {
                        $paths[] = $path . $width . DS . $entity->{$field};
                    }

                    return $paths;
                },
                'keepFilesOnDelete' => false, // Ensure files are deleted from disk when entity is deleted.
            ],
        ];

        // Add the Upload behavior if it's not already added to prevent re-adding.
        if (!$this->_table->hasBehavior('Josegonzalez/Upload.Upload')) {
            $this->_table->addBehavior('Josegonzalez/Upload.Upload', $uploadConfig);
        }
    }

    /**
     * beforeSave callback.
     *
     * Handles deletion of the old image file and its resized versions when a new image
     * is uploaded during an entity update.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @param \Cake\Datasource\EntityInterface $entity The entity being saved.
     * @param \ArrayObject $options Options for the save operation.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$entity->isNew() && $entity->isDirty($this->getConfig('field'))) {
            $originalImage = $entity->getOriginal($this->getConfig('field'));

            if ($originalImage) {
                $tableName = $this->_table->getTable();
                $field = $this->getConfig('field');
                $basePath = WWW_ROOT . 'files' . DS . ucfirst($tableName) . DS . $field . DS;

                $mainFilePath = $basePath . $originalImage;
                if (file_exists($mainFilePath)) {
                    unlink($mainFilePath);
                }

                $imageSizes = SettingsManager::read('ImageSizes', []);
                foreach ($imageSizes as $width) {
                    $resizedPath = $basePath . $width . DS . $originalImage;
                    if (file_exists($resizedPath)) {
                        unlink($resizedPath);
                    }
                }

                // Clear file stat cache after deletions
                clearstatcache();
            }
        }
    }

    /**
     * afterSave callback.
     *
     * Queues an image processing job and optionally an AI image analysis job
     * after an entity with an image is successfully saved.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @param \Cake\Datasource\EntityInterface $entity The entity that was saved.
     * @param \ArrayObject $options Options for the save operation.
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $config = $this->getConfig();
        // Check if the image field was changed/uploaded in this save operation
        if ($entity->isDirty($config['field'])) {
            $data = [
                'folder_path' => WWW_ROOT . $config['folder_path'],
                'file' => $entity->{$config['field']},
                'id' => $entity->id,
            ];

            // Queue up an image processing job to generate different sizes/versions.
            $this->_table->queueJob('App\Job\ProcessImageJob', $data);

            // Check if AI features are enabled for image analysis.
            if (SettingsManager::read('AI.enabled')) {
                // Add the model alias to the data for AI job.
                $data['model'] = $event->getSubject()->getAlias();

                // If image analysis is specifically enabled, queue that job to RabbitMQ.
                if (SettingsManager::read('AI.imageAnalysis')) {
                    $this->_table->queueJob('App\Job\ImageAnalysisJob', $data, [
                        'config' => 'rabbitmq',
                        'queue' => 'image_analysis',
                    ]);
                }
            }
        }
    }
}
