<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PageViews Model
 *
 * @property \App\Model\Table\ArticlesTable&\Cake\ORM\Association\BelongsTo $Articles
 * @method \App\Model\Entity\PageView newEmptyEntity()
 * @method \App\Model\Entity\PageView newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PageView> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PageView get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PageView findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PageView patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PageView> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PageView|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PageView saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PageView>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PageView>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PageView>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PageView> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PageView>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PageView>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PageView>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PageView> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PageViewsTable extends Table
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

        $this->setTable('page_views');
        $this->setDisplayField('ip_address');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'INNER',
        ]);

        $this->addBehavior('CounterCache', [
            'Articles' => [
                'view_count',
            ],
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
            ->uuid('article_id')
            ->notEmptyString('article_id');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address');

        $validator
            ->scalar('user_agent')
            ->allowEmptyString('user_agent');

        $validator
            ->scalar('referer')
            ->allowEmptyString('referer');

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
        $rules->add($rules->existsIn(['article_id'], 'Articles'), ['errorField' => 'article_id']);

        return $rules;
    }
}
