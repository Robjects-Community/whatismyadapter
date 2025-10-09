<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductFormFields Model
 *
 * @method \App\Model\Entity\ProductFormField newEmptyEntity()
 * @method \App\Model\Entity\ProductFormField newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductFormField> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ProductFormField get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ProductFormField findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ProductFormField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ProductFormField> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ProductFormField|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ProductFormField saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ProductFormField>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductFormField>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductFormField>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductFormField> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductFormField>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductFormField>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ProductFormField>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ProductFormField> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductFormFieldsTable extends Table
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

        $this->setTable('product_form_fields');
        $this->setDisplayField('field_label');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->scalar('field_name')
            ->maxLength('field_name', 100)
            ->requirePresence('field_name', 'create')
            ->notEmptyString('field_name')
            ->add('field_name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('field_label')
            ->maxLength('field_label', 255)
            ->requirePresence('field_label', 'create')
            ->notEmptyString('field_label');

        $validator
            ->scalar('field_type')
            ->maxLength('field_type', 50)
            ->requirePresence('field_type', 'create')
            ->notEmptyString('field_type');

        $validator
            ->scalar('field_placeholder')
            ->allowEmptyString('field_placeholder');

        $validator
            ->scalar('field_help_text')
            ->allowEmptyString('field_help_text');

        $validator
            ->allowEmptyString('field_options');

        $validator
            ->allowEmptyString('field_validation');

        $validator
            ->scalar('field_group')
            ->maxLength('field_group', 100)
            ->allowEmptyString('field_group');

        $validator
            ->integer('display_order')
            ->notEmptyString('display_order');

        $validator
            ->integer('column_width')
            ->notEmptyString('column_width');

        $validator
            ->boolean('is_required')
            ->notEmptyString('is_required');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        $validator
            ->boolean('ai_enabled')
            ->notEmptyString('ai_enabled');

        $validator
            ->scalar('ai_prompt_template')
            ->allowEmptyString('ai_prompt_template');

        $validator
            ->allowEmptyString('ai_field_mapping');

        $validator
            ->allowEmptyString('conditional_logic');

        $validator
            ->scalar('default_value')
            ->allowEmptyString('default_value');

        $validator
            ->scalar('css_classes')
            ->maxLength('css_classes', 255)
            ->allowEmptyString('css_classes');

        $validator
            ->allowEmptyString('html_attributes');

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
        $rules->add($rules->isUnique(['field_name']), ['errorField' => 'field_name']);

        return $rules;
    }
}
