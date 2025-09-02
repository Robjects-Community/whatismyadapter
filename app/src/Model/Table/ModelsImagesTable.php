<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ModelsImages Model
 *
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\BelongsTo $Images
 * @method \App\Model\Entity\ModelsImage newEmptyEntity()
 * @method \App\Model\Entity\ModelsImage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ModelsImage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ModelsImage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ModelsImage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ModelsImage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ModelsImage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ModelsImage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ModelsImage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ModelsImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ModelsImage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ModelsImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ModelsImage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ModelsImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ModelsImage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ModelsImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ModelsImage> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ModelsImagesTable extends Table
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

        $this->setTable('models_images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->scalar('model')
            ->maxLength('model', 255)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->uuid('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmptyString('foreign_key');

        $validator
            ->uuid('image_id')
            ->notEmptyString('image_id');

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
        $rules->add($rules->existsIn(['image_id'], 'Images'), ['errorField' => 'image_id']);

        return $rules;
    }
}
