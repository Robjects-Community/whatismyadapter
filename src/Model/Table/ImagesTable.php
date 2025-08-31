<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @method \App\Model\Entity\Image newEmptyEntity()
 * @method \App\Model\Entity\Image newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Image> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Image get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Image findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Image patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Image> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Image|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Image saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Image>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Image> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImagesTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use QueueableJobsTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.use Cake\Event\EventInterface;
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('images');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Images/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Timestamp');

        $this->belongsToMany('ImageGalleries', [
            'foreignKey' => 'image_id',
            'targetForeignKey' => 'image_gallery_id',
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
        $validator->notEmptyString('name', 'Name cannot be empty');

        return $validator;
    }

    /**
     * Validation rules for creating a new image.
     *
     * Extends the default validation rules and adds specific requirements for image creation:
     * - Requires the presence of an image file
     * - Validates the image file mime type (JPEG, PNG, or GIF)
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationCreate(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);

        return $this->addRequiredImageValidation($validator, 'image', [
            'messages' => [
                'mimeType' => 'Please upload only jpeg, png, or gif images.',
                'fileSize' => 'Image must be less than 10MB.',
                'required' => 'An image file is required',
            ],
        ]);
    }

    /**
     * Validation rules for updating an existing image.
     *
     * Extends the default validation rules and adds specific requirements for image updates:
     * - Allows the image file to be empty (no change)
     * - If a new image is provided, validates the mime type (JPEG, PNG, or GIF)
     * - Mime type validation only occurs when a new file is successfully uploaded
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationUpdate(Validator $validator): Validator
    {
        $validator = $this->validationDefault($validator);

        return $this->addOptionalImageValidation($validator, 'image', [
            'messages' => [
                'mimeType' => 'Please upload only jpeg, png, or gif images.',
                'fileSize' => 'Image must be less than 10MB.',
            ],
        ]);
    }
}
