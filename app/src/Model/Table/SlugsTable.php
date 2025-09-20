<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\App;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Exception;

/**
 * Slugs Model
 *
 * @method \App\Model\Entity\Slug newEmptyEntity()
 * @method \App\Model\Entity\Slug newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Slug> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Slug get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Slug findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Slug patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Slug> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Slug|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Slug saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Slug>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Slug> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SlugsTable extends Table
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

        $this->setTable('slugs');
        $this->setDisplayField('slug');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                ],
            ],
        ]);

        // Set up dynamic associations based on existing slugs
        $this->setupAssociations();
    }

    /**
     * Sets up dynamic associations based on the unique model values in the slugs table.
     * Uses cache to improve performance.
     *
     * @return void
     */
    protected function setupAssociations(): void
    {
        $models = $this->find()
            ->select(['model'])
            ->distinct()
            ->disableHydration()
            ->all()
            ->extract('model')
            ->toArray();

        foreach ($models as $model) {
            try {
                $className = App::className($model, 'Model/Table', 'Table');
                if ($className) {
                    $this->belongsTo($model, [
                        'className' => $className,
                        'foreignKey' => 'foreign_key',
                        'conditions' => [$this->getAlias() . '.model' => $model],
                        'joinType' => 'LEFT',
                    ]);
                }
            } catch (Exception $e) {
                $this->log(sprintf(
                    'Failed to setup association for model %s: %s',
                    $model,
                    $e->getMessage(),
                ), 'error');
            }
        }
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
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('model')
            ->maxLength('model', 20)
            ->requirePresence('model', 'create')
            ->notEmptyString('model');

        $validator
            ->uuid('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmptyString('foreign_key');

        $validator
            ->scalar('slug')
            ->maxLength('slug', 255)
            ->requirePresence('slug', 'create')
            ->notEmptyString('slug')
            ->regex(
                'slug',
                '/^[a-z0-9-]+$/',
                __('The slug must be URL-safe (only lowercase letters, numbers, and hyphens)'),
            );

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
        $rules->add($rules->isUnique(
            ['slug', 'model'],
            __('This slug is already in use for this model type.'),
        ));

        return $rules;
    }

    /**
     * Find by slug and model.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to modify
     * @param array $options The options containing slug and model
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findBySlugAndModel(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where([
            $this->getAlias() . '.slug' => $options['slug'],
            $this->getAlias() . '.model' => $options['model'],
        ]);
    }
}
